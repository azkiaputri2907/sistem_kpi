<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\NotifikasiPimpinanMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    // =========================================================================
    // HELPER SPREADSHEET API & DATA PROCESSING
    // =========================================================================

    private function getApiUrl()
    {
        return env('GOOGLE_SCRIPT_URL');
    }

    private function readSheet($sheetName)
    {
        $response = Http::get($this->getApiUrl(), [
            'action' => 'read',
            'sheet'  => $sheetName
        ]);
        $data = $response->json('data') ?? [];
        return collect(json_decode(json_encode($data), FALSE));
    }

    private function readMultipleSheets(array $sheets)
    {
        $url = $this->getApiUrl();
        $responses = Http::pool(function (Pool $pool) use ($sheets, $url) {
            $requests = [];
            foreach ($sheets as $sheet) {
                $requests[] = $pool->as($sheet)->get($url, ['action' => 'read', 'sheet' => $sheet]);
            }
            return $requests;
        });

        $result = [];
        foreach ($sheets as $sheet) {
            $response = $responses[$sheet];

            if ($response instanceof \Exception || !$response->successful()) {
                $data = [];
            } else {
                $data = $response->json('data') ?? [];
            }

            $result[$sheet] = collect(json_decode(json_encode($data), FALSE));
        }
        return $result;
    }

    private function createSheet($sheetName, $data)
    {
        return Http::post($this->getApiUrl() . '?action=create&sheet=' . $sheetName, $data)->json();
    }

    private function updateSheet($sheetName, $id, $data)
    {
        $data['id'] = $id;
        return Http::post($this->getApiUrl() . '?action=update&sheet=' . $sheetName, $data)->json();
    }

    private function deleteSheet($sheetName, $id)
    {
        return Http::post($this->getApiUrl() . '?action=delete&sheet=' . $sheetName, ['id' => $id])->json();
    }

    private function applyAccessFilter($collection, $user)
    {
        if ($user->role_id == 1 || $user->role_id == 3) {
            return $collection;
        }

        if (in_array($user->role_id, [2, 4]) && $user->prodi_id) {
            return $collection->filter(function($item) use ($user) {
                return $item->prodi_id == $user->prodi_id || $item->prodi_id == 'LAINNYA';
            })->values();
        }

        return collect([]);
    }

    // =========================================================================
    // HELPER: MENGAMANKAN SESSION USER
    // =========================================================================
    private function getSessionUser()
    {
        $sessionUser = session('user');
        return $sessionUser ? (object) $sessionUser : null;
    }


    // =========================================================================
    // CORE DASHBOARD CONTROLLER
    // =========================================================================

    public function index()
    {
        $user = $this->getSessionUser();

        if (!$user) {
            return redirect('/login');
        }

        if (!in_array($user->role_id, [1, 2])) {
            return $this->analytics();
        }

        $isGlobal =
            ($user->role_id == 1 ||
            $user->email === 'kajur.elektro@poliban.ac.id');

        $db = $this->readMultipleSheets([
            'kunjungan',
            'pengunjung',
            'master_prodi_instansi',
            'master_keperluan',
            'survey',
            'detail_survey'
        ]);

        $user->prodi = $db['master_prodi_instansi']
            ->firstWhere('id', $user->prodi_id);

        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis', 'Prodi')
            ->values();

        $kunjunganRaw = $this->applyAccessFilter(
            $db['kunjungan'],
            $user
        );

        if (request()->filled('prodi_id')) {
            $kunjunganRaw = $kunjunganRaw
                ->where('prodi_id', request('prodi_id'))
                ->values();
        }
        
        $hariIni = \Carbon\Carbon::now('Asia/Makassar')->format('Y-m-d'); 

        $kunjunganRaw = $kunjunganRaw->filter(function($item) use ($hariIni) {
            $tanggalData = \Carbon\Carbon::parse($item->created_at, 'Asia/Makassar')->format('Y-m-d');
            return $tanggalData === $hariIni;
        })->values();

        $kunjunganData = $kunjunganRaw->map(function($k) use ($db) {

            $k->pengunjung = $db['pengunjung']->firstWhere('id', $k->pengunjung_id);
            $k->prodi = $db['master_prodi_instansi']->firstWhere('id', $k->prodi_id);
            $k->keperluan_master = $db['master_keperluan']->firstWhere('id', $k->keperluan_id);
            $k->created_at = \Carbon\Carbon::parse($k->created_at ?? now());

            $k->durasi_layanan = '-';
            if (!empty($k->waktu_mulai_layanan) && !empty($k->waktu_selesai_layanan)) {
                $mulai = \Carbon\Carbon::parse($k->waktu_mulai_layanan);
                $selesai = \Carbon\Carbon::parse($k->waktu_selesai_layanan);
                $totalDetik = $mulai->diffInSeconds($selesai);
                
                $jam = floor($totalDetik / 3600);
                $menit = floor(($totalDetik % 3600) / 60);
                $detik = $totalDetik % 60;

                if ($jam > 0) {
                    $k->durasi_layanan = "{$jam} Jam {$menit} Mnt";
                } elseif ($menit > 0) {
                    $k->durasi_layanan = "{$menit} Mnt {$detik} Dtk";
                } else {
                    $k->durasi_layanan = "{$detik} Detik";
                }
            }
            return $k;
        })->sortByDesc('created_at')->values();

        $dataUlasan = $kunjunganData->filter(function($k) use ($db) {
            return $db['survey']->contains('kunjungan_id', $k->id);
        })->map(function($k) use ($db) {
            $survey = $db['survey']->firstWhere('kunjungan_id', $k->id);
            if ($survey) {
                $survey->detail = $db['detail_survey']->firstWhere('survey_id', $survey->id);
            }
            $k->survey = $survey;
            return $k;
        })->values();

        $tokenTerpakai = 0;
        foreach ($kunjunganData as $k) {
            if (strtoupper(trim($k->status_layanan ?? '')) != 'DITOLAK') {
                $hasSurvey = $db['survey']->contains('kunjungan_id', $k->id);
                
                if (strtoupper(trim($k->status_layanan ?? '')) != 'SELESAI' || !$hasSurvey) {
                    $tokenTerpakai++;
                }
            }
        }

        $totalKunjungan = $kunjunganData->count();
        $jumlahSelesai = $kunjunganData->filter(fn($i) => strtoupper(trim($i->status_layanan ?? '')) == 'SELESAI')->count();
        $jumlahDitolakKuantitas = $kunjunganData->filter(fn($i) => strtoupper(trim($i->status_layanan ?? '')) == 'DITOLAK')->count();
        $totalDilayani = $jumlahSelesai + $jumlahDitolakKuantitas;
        
        $targetTamu = 10; 
        $skorKuantitas = min(100, round($targetTamu > 0 ? ($totalDilayani / $targetTamu) * 100 : 0, 1)); 

        $jumlahTepatWaktu = $kunjunganData->filter(fn($i) => strtoupper(trim($i->status_sla ?? '')) == 'TEPAT WAKTU')->count();
        $jumlahTerlambat = $kunjunganData->filter(fn($i) => strtoupper(trim($i->status_sla ?? '')) == 'TERLAMBAT')->count();
        $jumlahDitolak = $jumlahDitolakKuantitas;

        $nilaiEfektivitas = ($jumlahTepatWaktu * 1) + ($jumlahTerlambat * 0.5) + ($jumlahDitolak * 0);
        $efektivitas = min(100, round($totalKunjungan > 0 ? ($nilaiEfektivitas / $totalKunjungan) * 100 : 0, 1));

        $persentasePenolakan = $totalKunjungan > 0 ? round(($jumlahDitolak / $totalKunjungan) * 100, 1) : 0;

        $totalSkorSurvey = 0;
        $jumlahResponden = 0;
        foreach ($kunjunganData as $k) {
            $surv = $db['survey']->firstWhere('kunjungan_id', $k->id);
            if ($surv && isset($surv->skor_total)) {
                $totalSkorSurvey += (int)$surv->skor_total;
                $jumlahResponden++;
            }
        }

        $kualitasRating = '-';
        $skorKualitas = 0;
        if ($jumlahResponden > 0) {
            $skorKualitas = min(100, round($totalSkorSurvey / $jumlahResponden, 1));
            $kualitasRating = number_format(round($skorKualitas / 20, 1), 1);
        }

        $kpiTotal = round((0.20 * $skorKuantitas) + (0.40 * $efektivitas) + (0.40 * $skorKualitas), 1);

        $kunjunganRaw->each(function($k) {
            if ($k->status_layanan == 'Antre' && \Carbon\Carbon::parse($k->created_at)->addMinutes(10)->isPast()) {
                $this->updateSheet('kunjungan', $k->id, [
                    'status_layanan' => 'Ditolak',
                    'alasan_tolak' => 'ADMIN TIDAK ADA DITEMPAT',
                    'waktu_selesai_layanan' => \Carbon\Carbon::now('Asia/Makassar')->toDateTimeString()
                ]);
                $k->status_layanan = 'Ditolak';
                $k->alasan_tolak = 'ADMIN TIDAK ADA DITEMPAT';
            }
        });

        return view('dashboard.index', [
            'user' => $user,
            'isGlobal' => $isGlobal,
            'judul_dashboard' => 'Dashboard Utama',
            'data_kunjungan' => $kunjunganData,
            'daftar_prodi' => $daftar_prodi,
            'data_ulasan' => $dataUlasan,
            'total_kunjungan' => $totalKunjungan,
            'total_dilayani' => $totalDilayani,
            'jumlah_tepat_waktu' => $jumlahTepatWaktu,
            'jumlah_terlambat' => $jumlahTerlambat,
            'jumlah_ditolak' => $jumlahDitolak,
            'skor_kuantitas' => $skorKuantitas,
            'efektivitas_persen' => $efektivitas,
            'kualitas_rating' => $kualitasRating,
            'skor_kualitas' => $skorKualitas,
            'kpi_total' => $kpiTotal,
            'persentase_penolakan' => $persentasePenolakan,
            'target_tamu' => $targetTamu,
            'token_terpakai' => $tokenTerpakai,
        ]);
    }

    public function cekTotal(Request $request)
    {
        $googleScriptUrl = env('GOOGLE_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbyjHp0Q2N5Rk-KzpMnZVnAng59gWLEuk2dRi0RrqFS5IwoumA1R8XzMjCP1T2kD6heKDQ/exec');

        $prodiId = $request->query('prodi_id', '');

        $response = Http::get($googleScriptUrl, [
            'action' => 'count_total',
            'prodi'  => $prodiId
        ]);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        return response()->json(['total_kunjungan' => 0], 500);
    }

public function analytics()
    {
        $user = $this->getSessionUser();

        if (!$user) {
            return redirect('/login');
        }

        $db = $this->readMultipleSheets([
            'kunjungan',
            'pengunjung',
            'survey',
            'detail_survey',
            'master_keperluan',
            'master_prodi_instansi'
        ]);

        $user->prodi = $db['master_prodi_instansi']
            ->firstWhere('id', $user->prodi_id);

        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis', 'Prodi')
            ->values();

        // 1. BASE QUERY: Mengambil semua data kunjungan berdasarkan filter Hak Akses
        $baseKunjunganQuery = $this->applyAccessFilter(
            $db['kunjungan'],
            $user
        );

        if (request()->filled('prodi_id')) {
            $baseKunjunganQuery = $baseKunjunganQuery
                ->where('prodi_id', request('prodi_id'))
                ->values();
        }

        // 2. QUERY HARIAN: Khusus untuk menghitung Kartu Statistik & Pie Chart (Sama seperti Dashboard)
        $hariIni = Carbon::now('Asia/Makassar')->format('Y-m-d'); 

        $kunjunganHarian = $baseKunjunganQuery->filter(function($item) use ($hariIni) {
            $tanggalKunjungan = Carbon::parse($item->created_at ?? now(), 'Asia/Makassar')->format('Y-m-d');
            return $tanggalKunjungan === $hariIni;
        })->values();

        // --- MENGHITUNG PIE CHART KEPUASAN (DARI DATA HARI INI) ---
        $sangatPuas = 0;
        $puas = 0;
        $kurangPuas = 0;
        $tidakPuas = 0;
        $totalCount = 0;

        foreach ($kunjunganHarian as $k) {
            $surv = $db['survey']->firstWhere('kunjungan_id', $k->id);

            if ($surv) {
                $detail = $db['detail_survey']->firstWhere('survey_id', $surv->id);

                if ($detail) {
                    $skorY = ($detail->p1 + $detail->p2 + $detail->p3 + $detail->p4 + $detail->p5) * 4;

                    if ($skorY >= 81) {
                        $sangatPuas++;
                    } elseif ($skorY >= 61) {
                        $puas++;
                    } elseif ($skorY >= 41) {
                        $kurangPuas++;
                    } else {
                        $tidakPuas++;
                    }
                    $totalCount++;
                }
            }
        }

        $totalPositif = $sangatPuas + $puas;
        $persentasePuas = $totalCount > 0 ? round(($totalPositif / $totalCount) * 100) : 0;
        $is_na = ($totalCount == 0);

        // --- MENGHITUNG KARTU STATISTIK ATAS (DARI DATA HARI INI) ---
        $totalKunjunganCard = $kunjunganHarian->count();

        $jumlahTepatWaktuCard = $kunjunganHarian->filter(function($item) {
            return strtoupper(trim($item->status_sla ?? '')) == 'TEPAT WAKTU';
        })->count();

        $jumlahTerlambatCard = $kunjunganHarian->filter(function($item) {
            return strtoupper(trim($item->status_sla ?? '')) == 'TERLAMBAT';
        })->count();

        $jumlahDitolakCard = $kunjunganHarian->filter(function($item) {
            return strtoupper(trim($item->status_layanan ?? '')) == 'DITOLAK';
        })->count();

        $nilaiEfektivitasCard = ($jumlahTepatWaktuCard * 1) + ($jumlahTerlambatCard * 0.5) + ($jumlahDitolakCard * 0);

        $efektivitasPersenCard = $totalKunjunganCard > 0
            ? round(($nilaiEfektivitasCard / $totalKunjunganCard) * 100, 1)
            : 0;
        $efektivitasPersenCard = max(0, min(100, $efektivitasPersenCard));

        $totalSkorSurveyCard = 0;
        $jumlahRespondenCard = 0;

        foreach ($kunjunganHarian as $k) {
            $surv = $db['survey']->firstWhere('kunjungan_id', $k->id);
            if ($surv && isset($surv->skor_total)) {
                $totalSkorSurveyCard += (int)$surv->skor_total;
                $jumlahRespondenCard++;
            }
        }

        $kualitasRatingCard = '-';
        if ($jumlahRespondenCard > 0) {
            $rataRataSkor = $totalSkorSurveyCard / $jumlahRespondenCard;
            $kualitasRatingCard = number_format(round($rataRataSkor / 20, 1), 1);
        }

        // --- MENGHITUNG DISTRIBUSI KEPERLUAN (DARI DATA HARI INI) ---
        $distribusiLabel = [];
        $distribusiData = [];

        $groupedKeperluan = $kunjunganHarian->groupBy('keperluan_id');

        foreach ($groupedKeperluan as $kep_id => $items) {
            $master = $db['master_keperluan']->firstWhere('id', $kep_id);

            if ($master) {
                $distribusiLabel[] = $master->keterangan;
                $distribusiData[] = $items->count();
            }
        }

        // =================================================================================
        // 3. GRAFIK TREN 7 HARI MUNDUR (TETAP MENGGUNAKAN BASE QUERY AGAR GRAFIK TIDAK RUSAK)
        // =================================================================================
        $tujuhHariLalu = Carbon::today('Asia/Makassar')->subDays(6)->startOfDay();

        $kunjunganSla = $baseKunjunganQuery->filter(function ($k) use ($tujuhHariLalu) {
            return !empty($k->status_sla) && Carbon::parse($k->created_at, 'Asia/Makassar')->gte($tujuhHariLalu);
        });

        $label_sla = [];
        $data_tepat_waktu = [];
        $data_terlambat = [];

        for ($i = 0; $i < 7; $i++) {
            $dateObj = Carbon::today('Asia/Makassar')->subDays(6 - $i);
            $dateStr = $dateObj->format('Y-m-d');
            $label_sla[] = $dateObj->format('d M');

            $data_tepat_waktu[] = $kunjunganSla->filter(function ($k) use ($dateStr) {
                return Carbon::parse($k->created_at, 'Asia/Makassar')->format('Y-m-d') == $dateStr
                    && strtoupper(trim($k->status_sla)) == 'TEPAT WAKTU';
            })->count();

            $data_terlambat[] = $kunjunganSla->filter(function ($k) use ($dateStr) {
                return Carbon::parse($k->created_at, 'Asia/Makassar')->format('Y-m-d') == $dateStr
                    && strtoupper(trim($k->status_sla)) == 'TERLAMBAT';
            })->count();
        }

        // =================================================================================
        // 4. GRAFIK KINERJA KPI 5 HARI KERJA (TETAP MENGGUNAKAN BASE QUERY)
        // =================================================================================
        $startDateParam = request('start_date');
        $referenceDate = $startDateParam ? Carbon::parse($startDateParam, 'Asia/Makassar') : Carbon::now('Asia/Makassar');

        $startOfWeek = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $referenceDate->copy()->endOfWeek(Carbon::FRIDAY)->endOfDay();

        $labels = [
            'Senin (' . $startOfWeek->format('d/m') . ')',
            'Selasa (' . $startOfWeek->copy()->addDays(1)->format('d/m') . ')',
            'Rabu (' . $startOfWeek->copy()->addDays(2)->format('d/m') . ')',
            'Kamis (' . $startOfWeek->copy()->addDays(3)->format('d/m') . ')',
            'Jumat (' . $startOfWeek->copy()->addDays(4)->format('d/m') . ')',
        ];

        $filteredProdiId = request('prodi_id');
        $prodisForChart = $filteredProdiId
            ? $daftar_prodi->where('id', $filteredProdiId)
            : $daftar_prodi;

        $chartDatasets = [];

        $grafikQuery = clone $baseKunjunganQuery;
        $grafikQuery = $grafikQuery->filter(function($k) use ($startOfWeek, $endOfWeek) {
            if (empty($k->created_at)) return false;
            $date = Carbon::parse($k->created_at, 'Asia/Makassar');
            return $date->gte($startOfWeek) && $date->lte($endOfWeek) && $date->dayOfWeekIso <= 5;
        });

        foreach ($prodisForChart as $prodi) {
            $prodiName = $prodi->nama ?? $prodi->nama_prodi ?? '-';

            $hariKpiSum = [0, 0, 0, 0, 0];
            $hariDataCount = [0, 0, 0, 0, 0];
            $prodiHariData = [0, 0, 0, 0, 0];

            $kunjunganProdi = $grafikQuery->where('prodi_id', $prodi->id);

            foreach ($kunjunganProdi as $data) {
                $createdDate = Carbon::parse($data->created_at ?? now(), 'Asia/Makassar');
                $dayOfWeek = $createdDate->dayOfWeekIso;

                if ($dayOfWeek > 5) continue; 
                $hariIndex = $dayOfWeek - 1;

                if (strtoupper(trim($data->status_layanan ?? '')) === 'DITOLAK' && empty($data->waktu_mulai_layanan)) {
                    $nilaiKpiGabunganRow = 0;
                } else {
                    $nilaiKuantitasSkala100 = (strtoupper(trim($data->status_layanan ?? '')) === 'SELESAI') ? 100 : 0;

                    $nama_pengunjung = $db['pengunjung']->firstWhere('id', $data->pengunjung_id)->nama_lengkap ?? null;
                    $skorKualitas = 0;
                    
                    if ($nama_pengunjung) {
                        $survey = $db['survey']->first(function($srv) use ($data, $nama_pengunjung) {
                            return (isset($srv->kunjungan_id) && $srv->kunjungan_id == $data->id)
                                   || (isset($srv->nama_lengkap) && $srv->nama_lengkap == $nama_pengunjung);
                        });
                        
                        if ($survey && isset($survey->skor_total)) {
                            $skorKualitas = floatval($survey->skor_total);
                        }
                    }
                    
                    $nilaiKualitasSkala100 = $skorKualitas <= 5 ? $skorKualitas * 20 : $skorKualitas;

                    $statusSlaRaw = isset($data->status_sla) ? strtoupper(trim($data->status_sla)) : '';
                    if ($statusSlaRaw === 'TEPAT WAKTU') {
                        $skorEfektivitas = 100;
                    } elseif ($statusSlaRaw === 'TERLAMBAT') {
                        $skorEfektivitas = 50; 
                    } else {
                        $skorEfektivitas = 0;
                    }

                    $nilaiKpiGabunganRow = ($nilaiKuantitasSkala100 * 0.20) + ($skorEfektivitas * 0.40) + ($nilaiKualitasSkala100 * 0.40);
                }

                $hariKpiSum[$hariIndex] += $nilaiKpiGabunganRow;
                $hariDataCount[$hariIndex]++;
            }

            $totalSkorPekan = array_sum($hariKpiSum);
            $totalDataPekan = array_sum($hariDataCount);
            $skorAkhirPekan = $totalDataPekan > 0 ? round($totalSkorPekan / $totalDataPekan, 1) : 0;

            $warnaProdiTunggal = '#94a3b8'; 
            if ($skorAkhirPekan >= 1 && $skorAkhirPekan <= 59) {
                $warnaProdiTunggal = '#ef4444'; 
            } elseif ($skorAkhirPekan >= 60 && $skorAkhirPekan <= 75) {
                $warnaProdiTunggal = '#f59e0b'; 
            } elseif ($skorAkhirPekan >= 76 && $skorAkhirPekan <= 90) {
                $warnaProdiTunggal = '#10b981'; 
            } elseif ($skorAkhirPekan > 90) {
                $warnaProdiTunggal = '#3b82f6'; 
            }

            for ($i = 0; $i < 5; $i++) {
                $prodiHariData[$i] = $hariDataCount[$i] > 0 ? round($hariKpiSum[$i] / $hariDataCount[$i], 1) : 0;
            }

            $chartDatasets[] = [
                'label'           => $prodiName,
                'data'            => $prodiHariData,
                'backgroundColor' => $warnaProdiTunggal,
                'borderRadius'    => 6,
                'borderSkipped'   => false,
                'barThickness'    => 14
            ];
        }
        
        return view('dashboard.analytics', [
            'user' => $user,
            'daftar_prodi' => $daftar_prodi,
            'is_na' => $is_na,
            'judul_dashboard' => 'Analytics KPI',

            'total_kunjungan' => $totalKunjunganCard,
            'efektivitas_persen' => $efektivitasPersenCard,
            'kualitas_rating' => $kualitasRatingCard,

            'skor_kepuasan' => [
                'sangat_puas' => $sangatPuas,
                'puas' => $puas,
                'kurang_puas' => $kurangPuas,
                'tidak_puas' => $tidakPuas,
                'persen' => $persentasePuas
            ],

            'distribusi_label' => $distribusiLabel,
            'distribusi_data' => $distribusiData,

            'label_sla' => $label_sla,
            'data_tepat_waktu' => $data_tepat_waktu,
            'data_terlambat' => $data_terlambat,

            'labels' => $labels,
            'chartDatasets' => $chartDatasets
        ]);
    }

    public function checkNotifications()
    {
        $user=(object) session('user');

        $db=$this->readMultipleSheets([
            'kunjungan'
        ]);

        $kunjungan=collect($db['kunjungan']);

        if(
            $user->role_id != 1 &&
            $user->email !== 'kajur.elektro@poliban.ac.id'
        ){
            $kunjungan=$kunjungan->where(
                'prodi_id',
                $user->prodi_id
            );
        }

        $pending=$kunjungan->filter(function($item){

            $status=strtoupper(
                trim($item->status_layanan ?? '')
            );

            return $status==='ANTRE';

        });

        return response()->json([

            'count'=>$pending->count(),

            'has_pending'=>$pending->count()>0

        ]);
    }

public function laporan(Request $request)
    {
        $user = $this->getSessionUser();
        if (!$user) return redirect('/login');

        $db = $this->readMultipleSheets([
            'kunjungan',
            'pengunjung',
            'master_prodi_instansi',
            'master_keperluan', 
            'survey'
        ]);

        $user->prodi = $db['master_prodi_instansi']
            ->firstWhere('id', $user->prodi_id);

        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis', 'Prodi')
            ->values();

        // 1. PISAHKAN BASE QUERY
        // Ini agar grafik mingguan tetap bisa membaca rentang 1 minggu utuh
        $baseKunjunganQuery = $this->applyAccessFilter($db['kunjungan'], $user);

        if(request()->filled('prodi_id')){
            $baseKunjunganQuery = $baseKunjunganQuery
                ->where('prodi_id', request('prodi_id'))
                ->values();
        }

        // 2. QUERY AKTIF UNTUK KARTU & TABEL
        $query = clone $baseKunjunganQuery;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // PERBAIKAN: Jika tidak ada filter tanggal, RESET otomatis ke hari ini
        if (empty($startDate) || empty($endDate)) {
            $startDate = Carbon::now('Asia/Makassar')->format('Y-m-d');
            $endDate = Carbon::now('Asia/Makassar')->format('Y-m-d');
        }

        $start = Carbon::parse($startDate, 'Asia/Makassar')->startOfDay();
        $end = Carbon::parse($endDate, 'Asia/Makassar')->endOfDay();

        // Saring data berdasarkan tanggal aktif
        $query = $query->filter(function($k) use ($start, $end) {
            $date = Carbon::parse($k->created_at ?? now(), 'Asia/Makassar');
            return $date->gte($start) && $date->lte($end);
        })->values();

        $totalSelesai = $query->where('status_layanan', 'Selesai')->count();
        $totalKunjungan = $query->count();
        $totalDitolak = $query->where('status_layanan', 'Ditolak')->count();
        $tingkatPenolakan = $totalKunjungan > 0 ? round(($totalDitolak / $totalKunjungan) * 100, 1) : 0;

        $kunjunganSelesai = $query->filter(function($k){
            return $k->status_layanan == 'Selesai'
                && !empty($k->waktu_mulai_layanan)
                && !empty($k->waktu_selesai_layanan);
        });

        $totalDetik = 0;
        foreach($kunjunganSelesai as $k){
            $totalDetik += Carbon::parse($k->waktu_mulai_layanan)
                ->diffInSeconds(Carbon::parse($k->waktu_selesai_layanan));
        }

        $rataDetik = $kunjunganSelesai->count() > 0
            ? round($totalDetik / $kunjunganSelesai->count())
            : 0;

        $menit = floor($rataDetik / 60);
        $detik = $rataDetik % 60;
        $rataRataSla = $menit.' menit '.$detik.' detik';

        // PERBAIKAN: Selalu memfilter pengunjung berdasarkan waktu aktif
        $pengunjungFiltered = $db['pengunjung']->filter(function($p) use ($start, $end) {
            if (empty($p->created_at)) return false;
            $date = Carbon::parse($p->created_at, 'Asia/Makassar');
            return $date->gte($start) && $date->lte($end);
        });
        
        $laporanPengunjung = $pengunjungFiltered->map(function($item) {
            return [
                'nama'          => $item->nama_lengkap ?? '-',
                'identitas_no'  => $item->identitas_no ?? '-',
                'no_telepon'    => $item->no_telepon ?? '-',
                'asal_instansi' => $item->asal_instansi ?? '-',
                'tanggal_masuk' => isset($item->created_at) ? date('Y-m-d', strtotime($item->created_at)) : '-',
            ];
        })->values();

        $laporanKunjungan = $query->map(function($item) use ($db) {
            $pengunjung = $db['pengunjung']->firstWhere('id', $item->pengunjung_id);
            $prodi = $db['master_prodi_instansi']->firstWhere('id', $item->prodi_id);
            $nama_prodi = $prodi->nama ?? $prodi->nama_prodi ?? '-';
            $masterKeperluan = $db['master_keperluan']->firstWhere('id', $item->keperluan_id);
            $keperluan_utama = $masterKeperluan->keterangan ?? 'Kunjungan Umum';

            return [
                'nomor_kunjungan'  => $item->nomor_kunjungan ?? '-',
                'nama'             => $pengunjung->nama_lengkap ?? '-',
                'prodi'            => $nama_prodi,
                'status_layanan'   => $item->status_layanan ?? '-',
                'keperluan_utama'  => $keperluan_utama,
                'keperluan_detail' => $item->keperluan ?? '-',
            ];
        })->values();

        $laporanKinerja = $query->map(function($item) use ($db) {
            $pengunjung = $db['pengunjung']->firstWhere('id', $item->pengunjung_id);
            $nama_pengunjung = $pengunjung->nama_lengkap ?? null;

            $skor_pelayanan = $item->skor_pelayanan ?? null;
            $skor_total_survey = '-';

            if (!empty($skor_pelayanan) && $nama_pengunjung) {
                $survey = $db['survey']->first(function($srv) use ($item, $nama_pengunjung) {
                    return (isset($srv->kunjungan_id) && $srv->kunjungan_id == $item->id)
                           || (isset($srv->nama_lengkap) && $srv->nama_lengkap == $nama_pengunjung);
                });
                $skor_total_survey = $survey->skor_total ?? '-';
            }

            $waktu_pengerjaan = '-';
            if (isset($item->estimasi_sla) && isset($item->satuan_sla)) {
                $waktu_pengerjaan = $item->estimasi_sla . ' ' . $item->satuan_sla;
            }

            return [
                'tanggal'           => isset($item->tanggal) ? date('Y-m-d', strtotime($item->tanggal)) : '-',
                'status_sla'        => $item->status_sla ?? '-',
                'skor_pelayanan'    => $skor_pelayanan ?? '-',
                'skor_total_survey' => $skor_total_survey,
                'waktu_pengerjaan'  => $waktu_pengerjaan,
            ];
        })->values();

        $laporanPenolakan = $query->filter(function($item) {
            return isset($item->status_layanan) && strtolower($item->status_layanan) == 'ditolak';
        })->map(function($item) use ($db) {
            $pengunjung = $db['pengunjung']->firstWhere('id', $item->pengunjung_id);
            $alasan_tolak = isset($item->alasan_tolak) ? ucwords(strtolower($item->alasan_tolak)) : '-';

            return [
                'nomor_kunjungan' => $item->nomor_kunjungan ?? '-',
                'nama'            => $pengunjung->nama_lengkap ?? '-',
                'asal_instansi'   => $pengunjung->asal_instansi ?? '-',
                'no_telepon'      => $pengunjung->no_telepon ?? '-',
                'alasan_penolakan'=> $alasan_tolak,
            ];
        })->values();

        // PERBAIKAN: Selalu memfilter survey berdasarkan waktu aktif
        $surveyFiltered = $db['survey']->filter(function($s) use ($start, $end) {
            $dateRaw = $s->created_at ?? $s->tanggal ?? null;
            if (!$dateRaw) return false;
            $date = Carbon::parse($dateRaw, 'Asia/Makassar');
            return $date->gte($start) && $date->lte($end);
        });

        Carbon::setLocale('id');
        $laporanUlasan = $surveyFiltered->map(function($item, $index) {
            $tanggal_raw = $item->created_at ?? $item->tanggal ?? null;
            $hari = '-';
            $tanggal = '-';

            if ($tanggal_raw) {
                $carbonDate = Carbon::parse($tanggal_raw, 'Asia/Makassar');
                $hari = $carbonDate->translatedFormat('l');
                $tanggal = $carbonDate->format('Y-m-d');
            }

            $ulasan = '-';
            if (!empty($item->kritik_saran)) {
                if (trim($item->kritik_saran) !== '' && !preg_match('/^[\*]+$/', trim($item->kritik_saran))) {
                    $ulasan = ucwords(strtolower($item->kritik_saran));
                }
            }

            return [
                'no'               => $index + 1,
                'hari'             => $hari,
                'tanggal'          => $tanggal,
                'nilai_kepuasan'   => $item->skor_total ?? '-',
                'ulasan'           => $ulasan,
            ];
        })->values();

        $referenceDate = Carbon::parse($startDate, 'Asia/Makassar');

        $startOfWeek = $referenceDate->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $endOfWeek = $referenceDate->copy()->endOfWeek(Carbon::FRIDAY)->endOfDay();

        $labels = [
            'Senin (' . $startOfWeek->format('d/m') . ')',
            'Selasa (' . $startOfWeek->copy()->addDays(1)->format('d/m') . ')',
            'Rabu (' . $startOfWeek->copy()->addDays(2)->format('d/m') . ')',
            'Kamis (' . $startOfWeek->copy()->addDays(3)->format('d/m') . ')',
            'Jumat (' . $startOfWeek->copy()->addDays(4)->format('d/m') . ')',
        ];

        $filteredProdiId = request('prodi_id');
        $prodisForChart = $filteredProdiId
            ? $daftar_prodi->where('id', $filteredProdiId)
            : $daftar_prodi;

        $chartDatasets = [];

        // PERBAIKAN: Menggunakan $baseKunjunganQuery agar grafik mingguan bisa menampilkan 5 hari utuh!
        $grafikQuery = clone $baseKunjunganQuery;
        $grafikQuery = $grafikQuery->filter(function($k) use ($startOfWeek, $endOfWeek) {
            if (empty($k->created_at)) return false;
            $date = Carbon::parse($k->created_at, 'Asia/Makassar');
            return $date->gte($startOfWeek) && $date->lte($endOfWeek);
        });

        foreach ($prodisForChart as $prodi) {
            $prodiName = $prodi->nama ?? $prodi->nama_prodi ?? '-';

            $hariKpiSum = [0, 0, 0, 0, 0];
            $hariDataCount = [0, 0, 0, 0, 0];
            $prodiHariData = [0, 0, 0, 0, 0];

            $kunjunganProdi = $grafikQuery->where('prodi_id', $prodi->id);

            foreach ($kunjunganProdi as $data) {
                $createdDate = Carbon::parse($data->created_at ?? now(), 'Asia/Makassar');
                $dayOfWeek = $createdDate->dayOfWeekIso;

                if ($dayOfWeek > 5) {
                    $dayOfWeek = 5;
                }
                $hariIndex = $dayOfWeek - 1;

                $skorKuantitas = isset($data->skor_pelayanan) ? floatval($data->skor_pelayanan) : 0;
                if ($skorKuantitas == 0) $skorKuantitas = 4.5;
                $nilaiKuantitasSkala100 = $skorKuantitas <= 5 ? $skorKuantitas * 20 : $skorKuantitas;

                $nama_pengunjung = $db['pengunjung']->firstWhere('id', $data->pengunjung_id)->nama_lengkap ?? null;
                $skorKualitas = 0;
                if ($nama_pengunjung) {
                    $survey = $db['survey']->first(function($srv) use ($data, $nama_pengunjung) {
                        return (isset($srv->kunjungan_id) && $srv->kunjungan_id == $data->id)
                               || (isset($srv->nama_lengkap) && $srv->nama_lengkap == $nama_pengunjung);
                    });
                    $skorKualitas = $survey ? floatval($survey->skor_total) : 0;
                }
                if ($skorKualitas == 0) $skorKualitas = 4.5;
                $nilaiKualitasSkala100 = $skorKualitas <= 5 ? $skorKualitas * 20 : $skorKualitas;

                $statusSlaRaw = isset($data->status_sla) ? strtoupper(trim($data->status_sla)) : '';
                if ($statusSlaRaw === 'TEPAT WAKTU' || $statusSlaRaw === '1') {
                    $skorEfektivitas = 100;
                } else {
                    $skorEfektivitas = 70;
                }

                $nilaiKpiGabunganRow = ($nilaiKuantitasSkala100 * 0.20) + ($skorEfektivitas * 0.40) + ($nilaiKualitasSkala100 * 0.40);

                $hariKpiSum[$hariIndex] += $nilaiKpiGabunganRow;
                $hariDataCount[$hariIndex]++;
            }

            for ($i = 0; $i < 5; $i++) {
                $prodiHariData[$i] = $hariDataCount[$i] > 0
                    ? round($hariKpiSum[$i] / $hariDataCount[$i], 1)
                    : 0;
            }

            $chartDatasets[] = [
                'label'           => $prodiName,
                'data'            => $prodiHariData,
                'backgroundColor' => '#6b7280',
                'borderRadius'    => 6,
                'borderSkipped'   => false,
                'barThickness'    => 14
            ];
        }

        // PERBAIKAN: Grafik Distribusi Kunjungan secara default akan terpengaruh $query harian saja
        $distribusiLabel = [];
        $distribusiData = [];

        $groupedKeperluan = $query->groupBy('keperluan_id');

        foreach ($groupedKeperluan as $kep_id => $items) {
            $master = $db['master_keperluan']->firstWhere('id', $kep_id);

            if ($master) {
                $distribusiLabel[] = $master->keterangan;
                $distribusiData[]  = $items->count();
            }
        }

        return view('dashboard.laporan', [
            'user'              => $user,
            'daftar_prodi'      => $daftar_prodi,
            'judul_dashboard'   => 'Laporan & Ekspor',
            'totalSelesai'      => $totalSelesai,
            'tingkatPenolakan'  => $tingkatPenolakan,
            'rataRataSla'       => $rataRataSla,
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'labels'            => $labels,
            'chartDatasets'     => $chartDatasets,
            'distribusi_label'  => $distribusiLabel,
            'distribusi_data'   => $distribusiData,
            'laporanPengunjung' => $laporanPengunjung,
            'laporanKunjungan'  => $laporanKunjungan,
            'laporanKinerja'    => $laporanKinerja,
            'laporanPenolakan'  => $laporanPenolakan,
            'laporanUlasan'     => $laporanUlasan
        ]);
    }

    private function exportLaporan($action, Request $request)
    {
        $user = $this->getSessionUser();

        $prodiParam = 'Semua-Prodi';

        if (in_array($user->role_id, [1, 3])) {
            if ($request->filled('prodi_id') && $request->prodi_id !== 'Semua-Prodi') {
                $prodiParam = $request->prodi_id;
            }
        } else {
            $prodiParam = $user->prodi_id;
        }

        if (!is_numeric($prodiParam) && $prodiParam !== 'Semua-Prodi') {
            $prodiStr = (string) $prodiParam;
            if (str_contains($prodiStr, 'Teknik Informatika')) { $prodiParam = '1'; }
            elseif (str_contains($prodiStr, 'Sistem Informasi Kota Cerdas')) { $prodiParam = '3'; }
            elseif (str_contains($prodiStr, 'Elektronika')) { $prodiParam = '7'; }
            elseif (str_contains($prodiStr, 'Teknik Listrik')) { $prodiParam = '8'; }
            elseif (str_contains($prodiStr, 'Pembangkit Energi')) { $prodiParam = '9'; }
            elseif (str_contains($prodiStr, 'Otomasi')) { $prodiParam = '10'; }
            else {
                $prodiParam = $user->prodi_id ?? 'Semua-Prodi';
            }
        }

        if (is_numeric($prodiParam)) {
            $prodiParam = (string) intval($prodiParam);
        }

        if (!$request->filled('start_date') || !$request->filled('end_date')) {
            return back()->with(
                'error',
                'Silakan pilih rentang tanggal terlebih dahulu.'
            );
        }

        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
        $type = $request->get('type', 'xlsx');

        $response = Http::get(
            $this->getApiUrl(),
            [
                'action'     => $action,
                'type'       => $type,
                'prodi'      => $prodiParam, 
                'start_date' => $startDate,
                'end_date'   => $endDate,
            ]
        );

        if (!$response->successful()) {
            dd('Request gagal ke Google API', $response->status(), $response->body());
        }

        $data = $response->json();

        if (!$data) {
            dd('JSON tidak valid dari Google API. Respons asli Google:', $response->body());
        }

        if (isset($data['status']) && $data['status'] === 'error') {
            return back()->with('error', 'Gagal Ekspor: ' . ($data['message'] ?? 'Data tidak ditemukan pada rentang tersebut.'));
        }

        if (!isset($data['url'])) {
            dd('URL file download tidak ditemukan pada respon data JSON', $data);
        }

        return redirect()->away($data['url']);
    }

    public function exportKunjungan(Request $request)
    {
        return $this->exportLaporan(
            'laporan_kunjungan',
            $request
        );
    }

    public function exportPengunjung(Request $request)
    {
        return $this->exportLaporan(
            'laporan_pengunjung',
            $request
        );
    }

    public function exportKinerja(Request $request)
    {
        return $this->exportLaporan(
            'laporan_kinerja',
            $request
        );
    }

    public function exportPenolakan(Request $request)
    {
        return $this->exportLaporan(
            'laporan_penolakan',
            $request
        );
    }

    public function exportUlasan(Request $request)
    {
        return $this->exportLaporan(
            'laporan_ulasan',
            $request
        );
    }


    public function ulasanLayanan()
    {
        $user = $this->getSessionUser();

        if (!$user) {
            return redirect('/login');
        }

        $db = $this->readMultipleSheets([
            'kunjungan',
            'pengunjung',
            'survey',
            'detail_survey',
            'master_prodi_instansi'
        ]);

        $user->prodi = $db['master_prodi_instansi']
            ->firstWhere('id',$user->prodi_id);

        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis','Prodi')
            ->values();

        $query = $this->applyAccessFilter(
            $db['kunjungan'],
            $user
        );

        if(request()->filled('prodi_id')){

            $query = $query
                ->where('prodi_id',request('prodi_id'))
                ->values();
        }

        $query = $query->filter(function($k) use ($db){

            return $db['survey']
                ->contains('kunjungan_id',$k->id);

        });

        $data_ulasan = $query->map(function($k) use ($db){

            $k->pengunjung = $db['pengunjung']
                ->firstWhere('id',$k->pengunjung_id);

            $survey = $db['survey']
                ->firstWhere('kunjungan_id',$k->id);

            if($survey){
                $survey->detail = $db['detail_survey']
                    ->firstWhere('survey_id',$survey->id);
            }

            $k->survey = $survey;

            $k->created_at = \Carbon\Carbon::parse(
                $k->created_at ?? now()
            );

            // LOGIKA TAMBAHAN: Tentukan jenis tamu
            $k->jenis_tamu = 'Eksternal';
            if ($k->pengunjung) {
                // Cek dari kolom kategori (jika ada) atau asal instansi
                $kategori = strtolower($k->pengunjung->kategori ?? '');
                $instansi = strtolower($k->pengunjung->asal_instansi ?? '');
                
                if (str_contains($kategori, 'internal') || $instansi === 'poliban') {
                    $k->jenis_tamu = 'Internal';
                }
            }

            return $k;

        })->sortByDesc('created_at')->values();

        return view('dashboard.ulasan',[
            'user'=>$user,
            'daftar_prodi'=>$daftar_prodi,
            'data_ulasan'=>$data_ulasan,
            'judul_dashboard'=>'Ulasan Pengunjung'
        ]);
    }

    // =========================================================================
    // AKSI PERUBAHAN DATA
    // =========================================================================

public function manajemenAntrean(Request $request)
    {
        $user = $this->getSessionUser();
        if (!$user) return redirect('/login');

        // 1. TAMBAHKAN 'survey' KE DALAM ARRAY READ MULTIPLE SHEETS
        $db = $this->readMultipleSheets([
            'kunjungan',
            'pengunjung',
            'master_prodi_instansi',
            'master_keperluan',
            'survey' // <--- Ini yang sebelumnya tertinggal
        ]);

        $user->prodi = $db['master_prodi_instansi']->firstWhere('id', $user->prodi_id);

        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis', 'Prodi')
            ->values();

        $query = $this->applyAccessFilter($db['kunjungan'], $user);

        if (request()->filled('prodi_id')) {
            $query = $query->where('prodi_id', request('prodi_id'))->values();
        }

        if ($request->has('search') && $request->search != '') {
            $search = strtolower($request->search);
            $query = $query->filter(function ($k) use ($search, $db) {
                $pengunjung = $db['pengunjung']->firstWhere('id', $k->pengunjung_id);
                return str_contains(strtolower($k->nomor_kunjungan ?? ''), $search)
                    || str_contains(strtolower($pengunjung->nama_lengkap ?? ''), $search);
            });
        }

        $now = Carbon::now('Asia/Makassar');

        $data_kunjungan = $query->map(function ($k) use ($db, $now) {
            $k->pengunjung = $db['pengunjung']->firstWhere('id', $k->pengunjung_id);
            $k->prodi = $db['master_prodi_instansi']->firstWhere('id', $k->prodi_id);
            $k->keperluan_master = $db['master_keperluan']->firstWhere('id', $k->keperluan_id);
            $k->created_at = Carbon::parse($k->created_at ?? now(), 'Asia/Makassar');

            // 2. MAPPING DATA SURVEY KE DALAM VARIABEL KUNJUNGAN
            // Agar file antrean.blade.php bisa mendeteksi apakah ulasan sudah diisi atau belum
$k->survey = $db['survey']->firstWhere('kunjungan_id', $k->id) 
             ?? $db['survey']->firstWhere('kunjungan_id', $k->nomor_kunjungan);

            $k->tipe_tamu = strtolower(trim($k->pengunjung->tipe_tamu ?? 'eksternal'));

            if (strcasecmp(trim($k->status_layanan ?? ''), 'Antre') === 0) {
                $batasRespon = $k->created_at->copy()->addMinutes(10);
                
                if ($now->greaterThan($batasRespon)) {
                    $this->updateSheet('kunjungan', $k->id, [
                        'status_layanan'        => 'Ditolak',
                        'status_sla'            => 'DITOLAK',
                        'alasan_tolak'          => 'Batas waktu tunggu habis. Petugas loket/admin saat ini sedang tidak berada di tempat.',
                        'waktu_selesai_layanan' => $now->toDateTimeString(),
                        'skor_pelayanan'        => 0
                    ]);

                    $k->status_layanan = 'Ditolak';
                    $k->status_sla     = 'DITOLAK';
                    $k->alasan_tolak   = 'Batas waktu tunggu habis. Petugas loket/admin saat ini sedang tidak berada di tempat.';
                    $k->waktu_selesai_layanan = $now->toDateTimeString();
                }
            }

            $k->durasi_layanan = '-';
            if (!empty($k->waktu_mulai_layanan) && !empty($k->waktu_selesai_layanan)) {
                $waktuMulai = Carbon::parse($k->waktu_mulai_layanan, 'Asia/Makassar');
                $waktuAkhir = Carbon::parse($k->waktu_selesai_layanan, 'Asia/Makassar');
                $totalDetik = $waktuMulai->diffInSeconds($waktuAkhir);

                $jam = floor($totalDetik / 3600);
                $menit = floor(($totalDetik % 3600) / 60);
                $detik = $totalDetik % 60;

                if ($jam > 0) {
                    $k->durasi_layanan = "{$jam} Jam {$menit} Mnt";
                } elseif ($menit > 0) {
                    $k->durasi_layanan = "{$menit} Mnt {$detik} Dtk";
                } else {
                    $k->durasi_layanan = "{$detik} Detik";
                }
            }

            return $k;
        })->sortByDesc('created_at')->values();

        return view('dashboard.antrean', [
            'user' => $user,
            'daftar_prodi' => $daftar_prodi,
            'data_kunjungan' => $data_kunjungan,
            'judul_dashboard' => 'Manajemen Antrean'
        ]);
    }

    public function mulaiProses($nomor_kunjungan)
    {
        $kunjungan = $this->readSheet('kunjungan')->firstWhere('nomor_kunjungan', $nomor_kunjungan);
        if (!$kunjungan) return back()->with('error', 'Data kunjungan tidak ditemukan.');

        $master = $this->readSheet('master_keperluan')->firstWhere('id', $kunjungan->keperluan_id);

        if (!$master) {
            $master = $this->readSheet('master_keperluan')->firstWhere('keterangan', $kunjungan->keperluan);
        }

        $estimasi_waktu = $master ? $master->estimasi_waktu : '30 Menit';
        $parts = explode(' ', trim($estimasi_waktu));
        $estimasi = (int) ($parts[0] ?? 30);
        $satuan = ucwords(strtolower($parts[1] ?? 'Menit'));

        $this->updateSheet('kunjungan', $kunjungan->id, [
            'status_layanan'      => 'Diproses',
            'estimasi_sla'        => $estimasi,
            'satuan_sla'          => $satuan,
            'user_id'             => $this->getSessionUser()->id ?? 0,
            'waktu_mulai_layanan' => Carbon::now('Asia/Makassar')->toDateTimeString(),
        ]);

        return back()->with('success', 'Antrean berhasil dimulai dengan estimasi waktu otomatis: ' . $estimasi . ' ' . $satuan);
    }

    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:255'
        ]);

        $this->updateSheet('kunjungan', $id, [
            'status_layanan' => 'Ditolak',
            'status_sla' => 'DITOLAK',

            'alasan_tolak' => $request->alasan_tolak,

            'user_id' => $this->getSessionUser()->id ?? 0,

            'waktu_selesai_layanan' => Carbon::now()->toDateTimeString(),

            'skor_pelayanan' => 0
        ]);

        return back()->with(
            'success',
            'Antrean berhasil ditolak.'
        );
    }

public function uploadFile(Request $request, $id)
    {
        $kunjungan = $this->readSheet('kunjungan')->firstWhere('id', $id);
            
        if (!$kunjungan) {
            return back()->with('error', 'Data tidak ditemukan');
        }

        $request->validate([
            'file_surat' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:4096'
        ]);

        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $ekstensi = $file->getClientOriginalExtension();
            $namaFile = 'surat_' . str_replace('-', '_', $kunjungan->nomor_kunjungan) . '_' . time() . '.' . $ekstensi;

            $fileBase64 = base64_encode(file_get_contents($file->getRealPath()));

            $urlGas = $this->getApiUrl();

            try {
                $response = Http::post($urlGas . '?action=upload_file', [
                    'action'      => 'upload_file',
                    'id'          => $id,
                    'nama_file'   => $namaFile,
                    'tipe_mime'   => $file->getMimeType(),
                    'file_base64' => $fileBase64
                ]);

                $hasil = $response->json();

                if (isset($hasil['status']) && $hasil['status'] === 'success') {
                    
                    $this->updateSheet('kunjungan', $kunjungan->id, [
                        'file_surat' => $hasil['link']
                    ]);

                    // UBAH 'success_upload_remind' MENJADI 'success'
                    // Agar memicu notifikasi Toast kustom bawaan tema, bukan SweetAlert
                    return back()->with(
                        'success',
                        'Berkas pendukung berhasil diunggah'
                    );
                } else {
                    $pesanError = $hasil['message'] ?? 'Respons Google Script tidak valid';
                    return back()->with('error', 'Gagal dari Google Script: ' . $pesanError);
                }

            } catch (\Exception $e) {
                return back()->with('error', 'Gagal menghubungi server Google: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Tidak ada file yang diunggah.');
    }

public function selesai($id)
    {
        $kunjungan = $this->readSheet('kunjungan')
            ->filter(function($k) use ($id) {
                return $k->id == $id || $k->nomor_kunjungan == $id;
            })
            ->first();

        if (!$kunjungan) {
            abort(404);
        }

        $waktuSelesai = Carbon::now();

        $estimasi = (int) ($kunjungan->estimasi_sla ?? 30);
        $satuan = $kunjungan->satuan_sla ?? 'Menit';

        $waktuMulai = !empty($kunjungan->waktu_mulai_layanan)
            ? Carbon::parse($kunjungan->waktu_mulai_layanan)
            : Carbon::parse($kunjungan->created_at);
            
        // --- LOGIKA SLA: HANYA HARI KERJA & JAM OPERASIONAL ---
        // 1 Hari Kerja = 7 Jam Efektif (08:00 - 12:00 dan 13:00 - 16:00) = 420 Menit
        $menitTersisa = (strtolower($satuan) == 'hari') ? ($estimasi * 420) : $estimasi;
        
        $batasWaktu = $waktuMulai->copy();
        
        // Loop penambahan waktu 1 menit ke depan
        while ($menitTersisa > 0) {
            $batasWaktu->addMinute();
            
            $jam = $batasWaktu->format('H:i');
            $hari = $batasWaktu->dayOfWeekIso; // 1=Senin, 5=Jumat, 6=Sabtu, 7=Minggu
            
            // 1. Cek Jam Operasional (Mati saat istirahat 12:00-13:00 dan di luar 08:00-16:00)
            $isJamKerja = (($jam >= '08:00' && $jam < '12:00') || ($jam >= '13:00' && $jam < '16:00'));
            
            // 2. Cek Hari Kerja (Mati saat Sabtu dan Minggu)
            $isHariKerja = ($hari >= 1 && $hari <= 5);
            
            // Menit SLA hanya berkurang kalau sedang jam kerja & hari kerja!
            if ($isHariKerja && $isJamKerja) {
                $menitTersisa--;
            }
        }
        // --------------------------------------------------------

        if ($waktuSelesai->greaterThan($batasWaktu)) {
            $statusSla = 'TERLAMBAT';
            $skorPelayanan = 0.5;
        } else {
            $statusSla = 'TEPAT WAKTU';
            $skorPelayanan = 1;
        }

        $this->updateSheet('kunjungan', $kunjungan->id, [
            'status_layanan' => 'Selesai',
            'user_id' => $this->getSessionUser()->id ?? 0,
            'waktu_selesai_layanan' => $waktuSelesai->toDateTimeString(),
            'status_sla' => $statusSla,
            'skor_pelayanan' => $skorPelayanan
        ]);

        return back()->with(
            'success',
            'Layanan selesai | SLA: ' . $statusSla
        );
    }

    public function kirimEmailPimpinan(Request $request)
    {
        $request->validate([
            'kunjungan_id' => 'required',
            'email_pimpinan' => 'nullable|email'
        ]);

        if (!$request->filled('email_pimpinan')) {
            return back()->with('success', 'Notifikasi WhatsApp Pimpinan berhasil dibuka!');
        }

        $db = $this->readMultipleSheets(['kunjungan', 'pengunjung', 'master_prodi_instansi', 'master_keperluan']);

        $kunjungan = $db['kunjungan']->first(function($item) use ($request) {
            return isset($item->id) && $item->id == $request->kunjungan_id;
        });

        if (!$kunjungan) return back()->with('error', 'Kunjungan tidak ditemukan');
        $kunjungan = (object) $kunjungan;

        $pengunjungData = $db['pengunjung']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == $kunjungan->pengunjung_id;
        });

        if ($pengunjungData) {
            $kunjungan->pengunjung = (object) $pengunjungData;
            $kunjungan->pengunjung->instansi = $kunjungan->pengunjung->asal_instansi ?? 'Umum / Mandiri';
        } else {
            $kunjungan->pengunjung = (object) ['nama_lengkap' => 'Umum', 'instansi' => 'Umum / Mandiri'];
        }

        $masterKeperluan = $db['master_keperluan']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == ($kunjungan->keperluan_id ?? null);
        });
        $kunjungan->nama_keperluan_utama = $masterKeperluan->keterangan ?? 'Kunjungan Umum';
        $kunjungan->keperluan_detail = !empty($kunjungan->keperluan) ? $kunjungan->keperluan : '-';

        $prodiData = $db['master_prodi_instansi']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == ($kunjungan->prodi_id ?? null);
        });
        
        $namaProdi = '-';
        if ($prodiData) {
            $prodiData = (object) $prodiData;
            $namaProdi = $prodiData->nama ?? $prodiData->nama_prodi ?? $prodiData->prodi ?? '-';
        }

        try {
            $urlGas = $this->getApiUrl();

            $response = Http::post($urlGas . '?action=kirim_email_pimpinan', [
                'email_pimpinan' => $request->email_pimpinan,
                'nomor_kunjungan' => $kunjungan->nomor_kunjungan,
                'nama_pengunjung' => $kunjungan->pengunjung->nama_lengkap,
                'instansi' => $kunjungan->pengunjung->instansi,
                'keperluan' => $kunjungan->nama_keperluan_utama,
                'detail' => $kunjungan->keperluan_detail,
                'prodi' => $namaProdi
            ]);

            $hasil = $response->json();

            if (isset($hasil['status']) && $hasil['status'] === 'success') {
                $this->updateSheet('kunjungan', $kunjungan->id, ['is_email_sent' => 1]);
                return back()->with('success', 'Email berhasil diteruskan ke pimpinan dan tautan WhatsApp diluncurkan!');
            } else {
                return back()->with('error', 'Gagal dikirim via GAS: ' . ($hasil['message'] ?? 'Error tidak diketahui'));
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghubungi server Google untuk email: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // CONTROL PANEL SETTINGS
    // =========================================================================
    public function controlPanel()
    {
        $user = $this->getSessionUser();
        if (!$user || $user->role_id != 1) return redirect()->route('dashboard')->with('error', 'Akses Ditolak');

        $db = $this->readMultipleSheets(['master_user', 'master_keperluan', 'master_role', 'master_prodi_instansi']);

        $rolesMap = collect($db['master_role'])->keyBy('id')->toArray();
        $prodiMap = collect($db['master_prodi_instansi'])->keyBy('id')->toArray();

        $mappedUsers = collect($db['master_user'])->map(function ($u) use ($rolesMap, $prodiMap) {
            $roleId = data_get($u, 'role_id');
            $prodiId = data_get($u, 'prodi_id');

            $u->nama_role = isset($rolesMap[$roleId]) ? data_get($rolesMap[$roleId], 'nama_role') : 'Tanpa Role';

            if (isset($prodiMap[$prodiId])) {
                $u->nama_prodi = data_get($prodiMap[$prodiId], 'nama');
            } else {
                $u->nama_prodi = $prodiId ?: null;
            }

            return $u;
        })->toArray();

        return view('dashboard.control_panel', [
            'user' => $user,
            'judul_dashboard' => 'Sistem Control Panel',
            'data_users' => $mappedUsers,
            'data_keperluan' => $db['master_keperluan'],
            'rolesRaw' => $db['master_role'],
            'prodiRaw' => $db['master_prodi_instansi']
        ]);
    }

    // =========================================================================
    // CRUD MANAGEMENT USER (MENYIMPAN ID ANGKA KE SPREADSHEET)
    // =========================================================================

    public function storeUser(Request $request)
    {
        $user = $this->getSessionUser();
        if (!$user || $user->role_id != 1) return redirect()->route('dashboard')->with('error', 'Akses Ditolak');

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'password' => 'required|min:6',
            'role_id'  => 'required',
            'prodi_id' => 'nullable' 
        ]);

        $usersRaw = $this->readSheet('master_user');
        $usersCollection = collect($usersRaw);

        if ($usersCollection->contains('email', $request->email)) {
            return back()->withErrors(['email' => 'Email ini sudah terdaftar di Spreadsheet.'])->withInput();
        }

        $lastId = $usersCollection->max('id') ?? 0;
        $newId = $lastId + 1;

        $payload = [
            'id'         => $newId,
            'role_id'    => $request->role_id,
            'prodi_id'   => $request->prodi_id ?? '', 
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s')
        ];

        $this->createSheet('master_user', $payload);

        return back()->with('success', 'User baru berhasil didaftarkan ke Google Sheets.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = $this->getSessionUser();
        if (!$user || $user->role_id != 1) return redirect()->route('dashboard')->with('error', 'Akses Ditolak');

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email',
            'role_id'  => 'required',
            'prodi_id' => 'nullable' 
        ]);

        $usersRaw = $this->readSheet('master_user');
        $usersCollection = collect($usersRaw);

        $emailTerpakai = $usersCollection->where('email', $request->email)->where('id', '!=', $id)->first();
        if ($emailTerpakai) {
            return back()->withErrors(['email' => 'Email ini sudah digunakan oleh user lain.'])->withInput();
        }

        $existingUser = $usersCollection->where('id', $id)->first();

        if ($existingUser) {
            $password = $request->filled('password') ? $request->password : (data_get($existingUser, 'password') ?? '');

            $payload = [
                'role_id'    => $request->role_id,
                'prodi_id'   => $request->prodi_id ?? '', 
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => $password,
                'created_at' => data_get($existingUser, 'created_at') ?? now()->format('Y-m-d H:i:s'),
                'updated_at' => now()->format('Y-m-d H:i:s')
            ];

            $this->updateSheet('master_user', $id, $payload);

            return back()->with('success', 'Data user di Google Sheets berhasil diperbarui.');
        }

        return back()->with('error', 'User tidak ditemukan di Google Sheets.');
    }

    public function destroyKeperluan($id)
    {
        $user = $this->getSessionUser();
        if (!$user || $user->role_id != 1) return abort(403);

        $kunjungan = $this->readSheet('kunjungan');
        $sedangDipakai = $kunjungan->contains('keperluan_id', $id);

        if ($sedangDipakai) {
            return back()->with('error', 'Gagal menghapus! Pilihan keperluan ini tidak bisa dihapus karena sedang digunakan oleh riwayat/antrean pengunjung.');
        }

        $this->deleteSheet('master_keperluan', $id);
        return back()->with('success', 'Pilihan keperluan berhasil dihapus.');
    }

    public function storeKeperluan(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
            'estimasi_jumlah' => 'required|numeric',
            'estimasi_satuan' => 'required|string'
        ]);

        $estimasi_gabungan = $request->estimasi_jumlah . ' ' . $request->estimasi_satuan;

        $this->createSheet('master_keperluan', [
            'keterangan' => $request->keterangan,
            'estimasi_waktu' => $estimasi_gabungan
        ]);

        return back()->with('success', 'Keperluan berhasil ditambahkan.');
    }

    public function destroyUser($id)
    {
        $user = $this->getSessionUser();
        if (!$user || $user->role_id != 1) return redirect()->route('dashboard')->with('error', 'Akses Ditolak');

        $this->deleteSheet('master_user', $id);

        return back()->with('success', 'User berhasil dihapus dari Google Sheets.');
    }

    public function tanggapanPimpinan(Request $request, $id)
    {
        $request->validate([
            'status_pimpinan' => 'required|in:Disetujui,Ditolak',
            'catatan_pimpinan' => 'nullable|string'
        ]);

        $kunjungan = $this->readSheet('kunjungan')->filter(function($k) use ($id) {
            return $k->id == $id || $k->nomor_kunjungan == $id;
        })->first();

        if (!$kunjungan) abort(404);

        $this->updateSheet('kunjungan', $kunjungan->id, [
            'status_pimpinan' => $request->status_pimpinan,
            'catatan_pimpinan' => $request->catatan_pimpinan ?? '-'
        ]);

        return back()->with('success', 'Tanggapan berhasil disimpan!');
    }
}