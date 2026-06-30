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

            // Cek apakah response berupa Exception (gagal koneksi) atau HTTP Error
            if ($response instanceof \Exception || !$response->successful()) {
                // Log error jika diperlukan (opsional)
                // \Log::error("Gagal mengambil data sheet {$sheet}: " . $response->getMessage());

                // Gunakan array kosong agar aplikasi tidak crash
                $data = [];
            } else {
                // Jika sukses, baru panggil ->json()
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
            return $collection->where('prodi_id', $user->prodi_id)->values();
        }

        return collect([]);
    }

    // =========================================================================
    // HELPER: MENGAMANKAN SESSION USER
    // =========================================================================
    private function getSessionUser()
    {
        $sessionUser = session('user');
        // Pastikan memaksa session menjadi Object agar tidak error "Attempt to read property on array"
        return $sessionUser ? (object) $sessionUser : null;
    }


    // =========================================================================
    // CORE DASHBOARD CONTROLLER
    // =========================================================================

    public function index()
{
    // Panggil fungsi pengaman session
    $user = $this->getSessionUser();

    if (!$user) {
        return redirect('/login');
    }

    // Jika bukan Admin / Super Admin
    if (!in_array($user->role_id, [1, 2])) {
        return $this->analytics();
    }

    $isGlobal =
        ($user->role_id == 1 ||
        $user->email === 'kajur.elektro@poliban.ac.id');

    // =========================================================
    // LOAD MULTIPLE SHEETS
    // =========================================================
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

    // =========================================================
    // DATA PRODI UNTUK FILTER DROPDOWN
    // =========================================================
    $daftar_prodi = $db['master_prodi_instansi']
        ->where('jenis', 'Prodi')
        ->values();

    // =========================================================
    // FILTER AKSES USER
    // =========================================================
    $kunjunganRaw = $this->applyAccessFilter(
        $db['kunjungan'],
        $user
    );

    // =========================================================
    // FILTER BERDASARKAN PRODI
    // =========================================================
    if (request()->filled('prodi_id')) {
        $kunjunganRaw = $kunjunganRaw
            ->where('prodi_id', request('prodi_id'))
            ->values();
    }
    
    // =========================================================
    // TAMBAHAN BARU: FILTER HANYA BULAN DAN TAHUN SEKARANG
    // =========================================================
    $bulanSekarang = Carbon::now()->format('m'); // Ambil angka bulan (misal: 07)
    $tahunSekarang = Carbon::now()->format('Y'); // Ambil angka tahun (misal: 2026)

    $kunjunganRaw = $kunjunganRaw->filter(function($item) use ($bulanSekarang, $tahunSekarang) {
        // Ambil data tanggal dari kolom 'tanggal' di Google Sheets
        $tanggalKunjungan = Carbon::parse($item->tanggal ?? now());
        
        // Hanya loloskan data yang bulan dan tahunnya cocok dengan hari ini
        return $tanggalKunjungan->format('m') === $bulanSekarang && 
               $tanggalKunjungan->format('Y') === $tahunSekarang;
    })->values();

    // =========================================================
    // MAPPING RELASI DATA
    // =========================================================
    $kunjunganData = $kunjunganRaw->map(function($k) use ($db) {

        $k->pengunjung =
            $db['pengunjung']
            ->firstWhere('id', $k->pengunjung_id);

        $k->prodi =
            $db['master_prodi_instansi']
            ->firstWhere('id', $k->prodi_id);

        $k->keperluan_master =
            $db['master_keperluan']
            ->firstWhere('id', $k->keperluan_id);

        $k->created_at =
            Carbon::parse($k->created_at ?? now());

        // =====================================================
        // FORMAT DURASI LAYANAN
        // =====================================================
        $k->durasi_layanan = '-';

        if (
            !empty($k->waktu_mulai_layanan) &&
            !empty($k->waktu_selesai_layanan)
        ) {

            $mulai =
                Carbon::parse($k->waktu_mulai_layanan);

            $selesai =
                Carbon::parse($k->waktu_selesai_layanan);

            $totalDetik =
                $mulai->diffInSeconds($selesai);

            $jam = floor($totalDetik / 3600);

            $menit =
                floor(($totalDetik % 3600) / 60);

            $detik =
                $totalDetik % 60;

            if ($jam > 0) {

                $k->durasi_layanan =
                    "{$jam} Jam {$menit} Mnt";

            } elseif ($menit > 0) {

                $k->durasi_layanan =
                    "{$menit} Mnt {$detik} Dtk";

            } else {

                $k->durasi_layanan =
                    "{$detik} Detik";
            }
        }

        return $k;

    })->sortByDesc('created_at')->values();

    // =========================================================
    // DATA ULASAN TERBARU (PROSES TAMBAHAN)
    // =========================================================
    // Mengambil data ulasan dari kunjungan yang sudah di-filter di atas
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

// =========================================================
    // KPI KUANTITAS (DENGAN TARGET 10 PENGUNJUNG BULANAN)
    // =========================================================
    $totalKunjungan = $kunjunganData->count();
    $totalDilayani = $kunjunganData->where('status_layanan', 'Selesai')->count();
    
    // Target ditetapkan 10 pengunjung sebulan
    $targetTamu = 10; 
    
    // Rumus: (Total Selesai / Target 10) * 100%
    $skorKuantitas = $targetTamu > 0 ? round(($totalDilayani / $targetTamu) * 100, 1) : 0;
    $skorKuantitas = max(0, min(100, $skorKuantitas)); // Batasi maksimal nilai 100%

    // =========================================================
    // KPI EFEKTIVITAS (SLA) - TETAP JAGA FUNGSI ASLI 100%
    // =========================================================
    $jumlahTepatWaktu = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_sla ?? '')) == 'TEPAT WAKTU';
    })->count();

    $jumlahTerlambat = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_sla ?? '')) == 'TERLAMBAT';
    })->count();

    $jumlahDitolak = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_layanan ?? '')) == 'DITOLAK';
    })->count();

    $nilaiEfektivitas = ($jumlahTepatWaktu * 1) + ($jumlahTerlambat * 0.5) + ($jumlahDitolak * 0);
    
    // Efektivitas pembaginya tetap total kunjungan bulan ini agar adil
    $efektivitas = $totalKunjungan > 0 ? round(($nilaiEfektivitas / $totalKunjungan) * 100, 1) : 0;
    $efektivitas = max(0, min(100, $efektivitas));

    // =========================================================
    // PERSENTASE PENOLAKAN
    // =========================================================
    $persentasePenolakan = $totalKunjungan > 0 ? round(($jumlahDitolak / $totalKunjungan) * 100, 1) : 0;

// =========================================================
    // KPI KUALITAS SURVEY (FIX MURNI SESUAI GAMBAR RUMUS LAPORAN TA)
    // =========================================================
    $totalSkorSurvey = 0;
    $jumlahResponden = 0;

    foreach ($kunjunganData as $k) {
        // Cari data survey yang terhubung dengan kunjungan ini
        $surv = $db['survey']->filter(function($s) use ($k) {
            return $s->kunjungan_id == $k->id;
        })->first();

        // Pastikan datanya ada dan kolom skor_total tidak kosong
        if ($surv && isset($surv->skor_total)) {
            $totalSkorSurvey += (int)$surv->skor_total;
            $jumlahResponden++;
        }
    }

    $kualitasRating = '-';
    $skorKualitas = 0;

    if ($jumlahResponden > 0) {
        // 1. Nilai KPI Kualitas Kinerja (Rata-rata Skor Total dari Google Sheets)
        // Contoh: (100 + 100) / 2 = 100
        $skorKualitas = round($totalSkorSurvey / $jumlahResponden, 1);
        $skorKualitas = max(0, min(100, $skorKualitas)); // Proteksi maksimal batas nilai 100

        // 2. Rumus dari Gambar TA Anda: Rata-rata Skor Total / 20
        // Contoh: 100 / 20 = 5.0
        $ratingAngka = $skorKualitas / 20;
        $kualitasRating = number_format(round($ratingAngka, 1), 1);
    }

    // =========================================================
    // TOTAL KPI
    // =========================================================
    $kpiTotal =
        (0.20 * $skorKuantitas) +
        (0.40 * $efektivitas) +
        (0.40 * $skorKualitas);

    $kpiTotal = round($kpiTotal, 1);

    // =========================================================
    // RETURN VIEW
    // =========================================================
    return view('dashboard.index', [

        'user' => $user,
        'isGlobal' => $isGlobal,
        'judul_dashboard' => 'Dashboard Utama',
        'data_kunjungan' => $kunjunganData,
        'daftar_prodi' => $daftar_prodi,

        // VARIABEL BARU: Di-passing agar terbaca di dashboard utama
        'data_ulasan' => $dataUlasan,

        // Statistik
        'total_kunjungan' => $totalKunjungan,
        'total_dilayani' => $totalDilayani,

        'jumlah_tepat_waktu' => $jumlahTepatWaktu,
        'jumlah_terlambat' => $jumlahTerlambat,
        'jumlah_ditolak' => $jumlahDitolak,

        // KPI
        'skor_kuantitas' => $skorKuantitas,
        'efektivitas_persen' => $efektivitas,
        'kualitas_rating' => $kualitasRating,
        'skor_kualitas' => $skorKualitas,
        'kpi_total' => $kpiTotal,

        // Tambahan
        'persentase_penolakan' => $persentasePenolakan,
        'target_tamu' => $targetTamu,
    ]);
}
public function cekTotal(Request $request)
{
    $googleScriptUrl = env('GOOGLE_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbz6QBns1Z3Sh1lhA5tgAJTOLL0sIdrTaudgNoSBitz3PrfCzH80vE36vMLkxTc10Lc1/exec');

    // Ambil input prodi_id dari request dashboard
    $prodiId = $request->query('prodi_id', '');

    // Tembak Google Apps Script dengan parameter yang sesuai dengan doGet()
    $response = Http::get($googleScriptUrl, [
        'action' => 'count_total',
        'prodi'  => $prodiId // Dikirim sebagai parameter 'prodi'
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

    $kunjunganData = $this->applyAccessFilter(
        $db['kunjungan'],
        $user
    );

    // FILTER PRODI
    if (request()->filled('prodi_id')) {
        $kunjunganData = $kunjunganData
            ->where('prodi_id', request('prodi_id'))
            ->values();
    }

    // =========================================================
    // TAMBAHAN BARU: FILTER HANYA BULAN DAN TAHUN SEKARANG
    // =========================================================
    $bulanSekarang = Carbon::now()->format('m'); 
    $tahunSekarang = Carbon::now()->format('Y'); 

    $kunjunganData = $kunjunganData->filter(function($item) use ($bulanSekarang, $tahunSekarang) {
        $tanggalKunjungan = Carbon::parse($item->tanggal ?? now());
        return $tanggalKunjungan->format('m') === $bulanSekarang && 
               $tanggalKunjungan->format('Y') === $tahunSekarang;
    })->values();

    // ==========================================
    // SKOR KEPUASAN (Fungsi Asli Dipertahankan)
    // ==========================================
    $sangatPuas = 0;
    $puas = 0;
    $kurangPuas = 0;
    $tidakPuas = 0;
    $totalCount = 0;

    foreach ($kunjunganData as $k) {
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

    // ==========================================
    // GRAFIK SLA (Fungsi Asli Dipertahankan)
    // ==========================================
    $tujuhHariLalu = Carbon::today()->subDays(6)->startOfDay();

    $kunjunganSla = $kunjunganData->filter(function ($k) use ($tujuhHariLalu) {
        return !empty($k->status_sla) && Carbon::parse($k->created_at)->gte($tujuhHariLalu);
    });

    $label_sla = [];
    $data_tepat_waktu = [];
    $data_terlambat = [];

    for ($i = 0; $i < 7; $i++) {
        $dateObj = Carbon::today()->subDays(6 - $i);
        $dateStr = $dateObj->format('Y-m-d');
        $label_sla[] = $dateObj->format('d M');

        $data_tepat_waktu[] = $kunjunganSla->filter(function ($k) use ($dateStr) {
            return Carbon::parse($k->created_at)->format('Y-m-d') == $dateStr
                && strtoupper($k->status_sla) == 'TEPAT WAKTU';
        })->count();

        $data_terlambat[] = $kunjunganSla->filter(function ($k) use ($dateStr) {
            return Carbon::parse($k->created_at)->format('Y-m-d') == $dateStr
                && strtoupper($k->status_sla) == 'TERLAMBAT';
        })->count();
    }

    // ==========================================
    // DISTRIBUSI KEPERLUAN (Fungsi Asli Dipertahankan)
    // ==========================================
    $distribusiLabel = [];
    $distribusiData = [];

    $groupedKeperluan = $kunjunganData->groupBy('keperluan_id');

    foreach ($groupedKeperluan as $kep_id => $items) {
        $master = $db['master_keperluan']->firstWhere('id', $kep_id);

        if ($master) {
            $distribusiLabel[] = $master->keterangan;
            $distribusiData[] = $items->count();
        }
    }

    // ==========================================
    // CARD STATISTIK ANALYTICS (MENGGUNAKAN RUMUS SKOR TOTAL REVISI)
    // ==========================================
    $totalKunjunganCard = $kunjunganData->count();

    $jumlahTepatWaktuCard = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_sla ?? '')) == 'TEPAT WAKTU';
    })->count();

    $jumlahTerlambatCard = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_sla ?? '')) == 'TERLAMBAT';
    })->count();

    $jumlahDitolakCard = $kunjunganData->filter(function($item) {
        return strtoupper(trim($item->status_layanan ?? '')) == 'DITOLAK';
    })->count();

    $nilaiEfektivitasCard = ($jumlahTepatWaktuCard * 1) + ($jumlahTerlambatCard * 0.5) + ($jumlahDitolakCard * 0);

    $efektivitasPersenCard = $totalKunjunganCard > 0
        ? round(($nilaiEfektivitasCard / $totalKunjunganCard) * 100, 1)
        : 0;
    $efektivitasPersenCard = max(0, min(100, $efektivitasPersenCard));

    $totalSkorSurveyCard = 0;
    $jumlahRespondenCard = 0;

    foreach ($kunjunganData as $k) {
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

// =========================================================================
    // KODE GRAFIK KINERJA PEKANAN (SESUAI RUMUS MURNI BAB 3 LAPORAN TA)
    // =========================================================================

    $startDateParam = request('start_date');
    $referenceDate = $startDateParam ? Carbon::parse($startDateParam) : Carbon::now();

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

    // Filter query: Ambil data rentang Senin s.d Jumat
    $grafikQuery = $kunjunganData->filter(function($k) use ($startOfWeek, $endOfWeek) {
        if (empty($k->created_at)) return false;
        $date = Carbon::parse($k->created_at);
        return $date->gte($startOfWeek) && $date->lte($endOfWeek) && $date->dayOfWeekIso <= 5;
    });

    foreach ($prodisForChart as $prodi) {
        $prodiName = $prodi->nama ?? $prodi->nama_prodi ?? '-';

        $hariKpiSum = [0, 0, 0, 0, 0];
        $hariDataCount = [0, 0, 0, 0, 0];
        $prodiHariData = [0, 0, 0, 0, 0];
        $prodiHariColors = ['#94a3b8', '#94a3b8', '#94a3b8', '#94a3b8', '#94a3b8']; // Default Abu-abu (slate-400) untuk N/A

        $kunjunganProdi = $grafikQuery->where('prodi_id', $prodi->id);

        foreach ($kunjunganProdi as $data) {
            $createdDate = Carbon::parse($data->created_at ?? now());
            $dayOfWeek = $createdDate->dayOfWeekIso;

            if ($dayOfWeek > 5) continue; 
            $hariIndex = $dayOfWeek - 1;

            // Jika status layanan adalah DITOLAK di loket depan (seperti data ID 18), 
            // maka murni dianggap tidak ada kinerja / Not Applicable (0)
            if (strtoupper(trim($data->status_layanan ?? '')) === 'DITOLAK' && empty($data->waktu_mulai_layanan)) {
                $nilaiKpiGabunganRow = 0;
            } else {
                // --- 1. ASPEK KUANTITAS (Bobot 20%) ---
                // Selesai diproses = 100, Jika masih antre/batal/kosong = 0
                $nilaiKuantitasSkala100 = (strtoupper(trim($data->status_layanan ?? '')) === 'SELESAI') ? 100 : 0;

                // --- 2. ASPEK KUALITAS (Bobot 40%) ---
                // Konversi nilai rata-rata bintang ke skala 100 murni sesuai rumus laporan TA
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
                
                // Jika skorKualitas <= 5 (berupa rating bintang dari user, misal 4.2), konversi ke skala 100 murni
                $nilaiKualitasSkala100 = $skorKualitas <= 5 ? $skorKualitas * 20 : $skorKualitas;

                // --- 3. ASPEK EFEKTIVITAS SLA (Bobot 40%) ---
                $statusSlaRaw = isset($data->status_sla) ? strtoupper(trim($data->status_sla)) : '';
                if ($statusSlaRaw === 'TEPAT WAKTU') {
                    $skorEfektivitas = 100;
                } elseif ($statusSlaRaw === 'TERLAMBAT') {
                    $skorEfektivitas = 50; // Sesuai pembobotan 0.5 di Controller utama kamu
                } else {
                    $skorEfektivitas = 0;
                }

                // Rumus Gabungan Bobot Berdasarkan Tabel 3.4 Laporan TA Kamu
                $nilaiKpiGabunganRow = ($nilaiKuantitasSkala100 * 0.20) + ($skorEfektivitas * 0.40) + ($nilaiKualitasSkala100 * 0.40);
            }

            $hariKpiSum[$hariIndex] += $nilaiKpiGabunganRow;
            $hariDataCount[$hariIndex]++;
        }

        // Kalkulasi rata-rata nilai harian & penentuan warna dinamis berdasarkan tabel rentang nilai TA
        for ($i = 0; $i < 5; $i++) {
            $skorAkhirHari = $hariDataCount[$i] > 0 ? round($hariKpiSum[$i] / $hariDataCount[$i], 1) : 0;
            $prodiHariData[$i] = $skorAkhirHari;

            // Logika Penentuan Warna Sesuai Aturan Visualisasi Tabel 3.3 Laporan TA Kamu
            if ($skorAkhirHari == 0) {
                $prodiHariColors[$i] = '#94a3b8'; // N/A (Abu-abu / Slate-400)
            } elseif ($skorAkhirHari >= 1 && $skorAkhirHari <= 59) {
                $prodiHariColors[$i] = '#ef4444'; // Kurang (Merah / Red-500)
            } elseif ($skorAkhirHari >= 60 && $skorAkhirHari <= 75) {
                $prodiHariColors[$i] = '#f59e0b'; // Cukup (Amber / Amber-500)
            } elseif ($skorAkhirHari >= 76 && $skorAkhirHari <= 90) {
                $prodiHariColors[$i] = '#10b981'; // Baik (Emerald / Emerald-500)
            } elseif ($skorAkhirHari > 90) {
                $prodiHariColors[$i] = '#3b82f6'; // Sangat Baik (Biru / Blue-500)
            }
        }

        $chartDatasets[] = [
            'label'           => $prodiName,
            'data'            => $prodiHariData,
            'backgroundColor' => $prodisForChart->count() === 1 ? $prodiHariColors : $prodiHariColors[0], // Warna dinamis per batang jika single prodi
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

    // filter prodi
    if(
        $user->role_id != 1 &&
        $user->email !== 'kajur.elektro@poliban.ac.id'
    ){
        $kunjungan=$kunjungan->where(
            'prodi_id',
            $user->prodi_id
        );
    }

    // hanya status ANTRE
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

    // 1. Memuat seluruh sheet data yang dibutuhkan untuk laporan dan grafik
    $db = $this->readMultipleSheets([
        'kunjungan',
        'pengunjung',
        'master_prodi_instansi',
        'master_keperluan', // Tetap dipertahankan untuk grafik distribusi keperluan
        'survey'
    ]);

    $user->prodi = $db['master_prodi_instansi']
        ->firstWhere('id', $user->prodi_id);

    $daftar_prodi = $db['master_prodi_instansi']
        ->where('jenis', 'Prodi')
        ->values();

    $query = $this->applyAccessFilter($db['kunjungan'], $user);

    // FILTER PRODI
    if(request()->filled('prodi_id')){
        $query = $query
            ->where('prodi_id', request('prodi_id'))
            ->values();
    }

    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $start = null;
    $end = null;

    // Filter Tanggal Utama (Carbon)
    if ($startDate && $endDate) {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        $query = $query->filter(function($k) use ($start, $end) {
            $date = Carbon::parse($k->created_at);
            return $date->gte($start) && $date->lte($end);
        });
    }

    // Perhitungan Statistik Utama
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

    // ==========================================
    // PROSES GENERASI 5 LAPORAN DATA
    // ==========================================

    // 1. LAPORAN PENGUNJUNG
    $pengunjungFiltered = $db['pengunjung'];
    if ($startDate && $endDate) {
        $pengunjungFiltered = $pengunjungFiltered->filter(function($p) use ($start, $end) {
            if (empty($p->created_at)) return false;
            $date = Carbon::parse($p->created_at);
            return $date->gte($start) && $date->lte($end);
        });
    }
    $laporanPengunjung = $pengunjungFiltered->map(function($item) {
        return [
            'nama'          => $item->nama_lengkap ?? '-',
            'identitas_no'  => $item->identitas_no ?? '-',
            'no_telepon'    => $item->no_telepon ?? '-',
            'asal_instansi' => $item->asal_instansi ?? '-',
            'tanggal_masuk' => isset($item->created_at) ? date('Y-m-d', strtotime($item->created_at)) : '-',
        ];
    })->values();

    // 2. LAPORAN KUNJUNGAN
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

    // 3. LAPORAN KINERJA ADMIN
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

    // 4. LAPORAN PENOLAKAN
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

    // 5. LAPORAN ULASAN
    $surveyFiltered = $db['survey'];
    if ($startDate && $endDate) {
        $surveyFiltered = $surveyFiltered->filter(function($s) use ($start, $end) {
            $dateRaw = $s->created_at ?? $s->tanggal ?? null;
            if (!$dateRaw) return false;
            $date = Carbon::parse($dateRaw);
            return $date->gte($start) && $date->lte($end);
        });
    }

    Carbon::setLocale('id');
    $laporanUlasan = $surveyFiltered->map(function($item, $index) {
        $tanggal_raw = $item->created_at ?? $item->tanggal ?? null;
        $hari = '-';
        $tanggal = '-';

        if ($tanggal_raw) {
            $carbonDate = Carbon::parse($tanggal_raw);
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

    // ==========================================
    // LOGIKA GRAFIK KINERJA PEKANAN (Bawaan Laporan)
    // ==========================================
    $referenceDate = $startDate ? Carbon::parse($startDate) : Carbon::now();

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

    $grafikQuery = clone $query;
    $grafikQuery = $grafikQuery->filter(function($k) use ($startOfWeek, $endOfWeek) {
        if (empty($k->created_at)) return false;
        $date = Carbon::parse($k->created_at);
        return $date->gte($startOfWeek) && $date->lte($endOfWeek);
    });

    foreach ($prodisForChart as $prodi) {
        $prodiName = $prodi->nama ?? $prodi->nama_prodi ?? '-';

        $hariKpiSum = [0, 0, 0, 0, 0];
        $hariDataCount = [0, 0, 0, 0, 0];
        $prodiHariData = [0, 0, 0, 0, 0];

        $kunjunganProdi = $grafikQuery->where('prodi_id', $prodi->id);

        foreach ($kunjunganProdi as $data) {
            $createdDate = Carbon::parse($data->created_at ?? now());
            $dayOfWeek = $createdDate->dayOfWeekIso;

            if ($dayOfWeek > 5) {
                $dayOfWeek = 5;
            }
            $hariIndex = $dayOfWeek - 1;

            // 1. Kuantitas
            $skorKuantitas = isset($data->skor_pelayanan) ? floatval($data->skor_pelayanan) : 0;
            if ($skorKuantitas == 0) $skorKuantitas = 4.5;
            $nilaiKuantitasSkala100 = $skorKuantitas <= 5 ? $skorKuantitas * 20 : $skorKuantitas;

            // 2. Kualitas
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

            // 3. SLA
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

    // =========================================================================
    // KODE BARU: LOGIKA KODE KHUSUS GRAFIK DISTRIBUSI KEPERLUAN (DARI ANALYTICS)
    // =========================================================================
    $distribusiLabel = [];
    $distribusiData = [];

    // Mengelompokkan data berdasarkan query kunjungan yang aktif (mendukung filter tanggal & prodi)
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

        // Data Grafik Kinerja Pekanan (Tetap Utuh)
        'labels'            => $labels,
        'chartDatasets'     => $chartDatasets,

        // Tambahan Variabel Baru untuk Injeksi Grafik Keperluan di laporan.blade.php
        'distribusi_label'  => $distribusiLabel,
        'distribusi_data'   => $distribusiData,

        // Data 5 jenis tabel laporan (Tetap Utuh)
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

    // =====================================
    // PRODI (Sanitasi & Ambil ID Angka Saja)
    // =====================================
    $prodiParam = 'Semua-Prodi';

    if (in_array($user->role_id, [1, 3])) {
        // Super Admin & Kajur bisa memilih prodi melalui dropdown filter
        if ($request->filled('prodi_id') && $request->prodi_id !== 'Semua-Prodi') {
            $prodiParam = $request->prodi_id;
        }
    } else {
        // Admin & Kaprodi otomatis terkunci ke ID prodi miliknya sendiri
        $prodiParam = $user->prodi_id;
    }

    // --- PROTEKSI TAMBAHAN: JURUS SANITASI REGEX & TEXT MAPPING ---
    // Jika karena suatu hal parameter masih mengandung teks/emoji prodi, bersihkan di sini
    if (!is_numeric($prodiParam) && $prodiParam !== 'Semua-Prodi') {
        $prodiStr = (string) $prodiParam;
        if (str_contains($prodiStr, 'Teknik Informatika')) { $prodiParam = '1'; }
        elseif (str_contains($prodiStr, 'Sistem Informasi Kota Cerdas')) { $prodiParam = '3'; }
        elseif (str_contains($prodiStr, 'Elektronika')) { $prodiParam = '7'; }
        elseif (str_contains($prodiStr, 'Teknik Listrik')) { $prodiParam = '8'; }
        elseif (str_contains($prodiStr, 'Pembangkit Energi')) { $prodiParam = '9'; }
        elseif (str_contains($prodiStr, 'Otomasi')) { $prodiParam = '10'; }
        else {
            // Jika benar-benar tidak dikenali atau kosong, amankan ke ID prodi milik user login
            $prodiParam = $user->prodi_id ?? 'Semua-Prodi';
        }
    }

    // Pastikan variabel hanya menyisakan Angka murni atau string 'Semua-Prodi'
    if (is_numeric($prodiParam)) {
        $prodiParam = (string) intval($prodiParam);
    }

    // =====================================
    // VALIDASI FILTER TANGGAL
    // =====================================
    if (!$request->filled('start_date') || !$request->filled('end_date')) {
        return back()->with(
            'error',
            'Silakan pilih rentang tanggal terlebih dahulu.'
        );
    }

    // Pastikan format tanggal bersih YYYY-MM-DD
    $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
    $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
    $type = $request->get('type', 'xlsx');

    // =====================================
    // KIRIM KE APPS SCRIPT
    // =====================================
    $response = Http::get(
        $this->getApiUrl(),
        [
            'action'     => $action,
            'type'       => $type,
            'prodi'      => $prodiParam, // DIJAMIN BERSIH: Mengirim '1', '3', dst, atau 'Semua-Prodi'
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

// ======================================================
// EXPORT KUNJUNGAN
// ======================================================

public function exportKunjungan(Request $request)
{
    return $this->exportLaporan(
        'laporan_kunjungan',
        $request
    );
}

// ======================================================
// EXPORT PENGUNJUNG
// ======================================================

public function exportPengunjung(Request $request)
{
    return $this->exportLaporan(
        'laporan_pengunjung',
        $request
    );
}

// ======================================================
// EXPORT KINERJA
// ======================================================

public function exportKinerja(Request $request)
{
    return $this->exportLaporan(
        'laporan_kinerja',
        $request
    );
}

// ======================================================
// EXPORT PENOLAKAN
// ======================================================

public function exportPenolakan(Request $request)
{
    return $this->exportLaporan(
        'laporan_penolakan',
        $request
    );
}

// ======================================================
// EXPORT ULASAN
// ======================================================

public function exportUlasan(Request $request)
{
    return $this->exportLaporan(
        'laporan_ulasan',
        $request
    );
}

public function manajemenAntrean(Request $request)
{
    $user = $this->getSessionUser();
    if (!$user) return redirect('/login');

    $db = $this->readMultipleSheets([
        'kunjungan',
        'pengunjung',
        'master_prodi_instansi',
        'master_keperluan'
    ]);

    // Set relasi prodi ke object user agar pas di view blade (untuk user non-admin)
    $user->prodi = $db['master_prodi_instansi']
        ->firstWhere('id', $user->prodi_id);

    // Ambil daftar prodi khusus yang bertipe 'Prodi' saja agar dropdown terisi
    $daftar_prodi = $db['master_prodi_instansi']
        ->where('jenis', 'Prodi')
        ->values();

    $query = $this->applyAccessFilter($db['kunjungan'], $user);

    // FILTER PRODI (Sesuai dengan logika fungsi laporan Anda)
    if (request()->filled('prodi_id')) {
        $query = $query
            ->where('prodi_id', request('prodi_id'))
            ->values();
    }

    // FILTER PENCARIAN NAMA / NOMOR KUNJUNGAN
    if ($request->has('search') && $request->search != '') {
        $search = strtolower($request->search);

        $query = $query->filter(function ($k) use ($search, $db) {
            $pengunjung = $db['pengunjung']
                ->firstWhere('id', $k->pengunjung_id);

            return str_contains(strtolower($k->nomor_kunjungan ?? ''), $search)
                || str_contains(strtolower($pengunjung->nama_lengkap ?? ''), $search);
        });
    }

    // MAP DATA DAN RELASI MANUAL UNTUK TABEL ANTREAN
    $data_kunjungan = $query->map(function ($k) use ($db) {

        $k->pengunjung = $db['pengunjung']
            ->firstWhere('id', $k->pengunjung_id);

        $k->prodi = $db['master_prodi_instansi']
            ->firstWhere('id', $k->prodi_id);

        $k->keperluan_master = $db['master_keperluan']
            ->firstWhere('id', $k->keperluan_id);

        // Format tanggal menggunakan Carbon
        $k->created_at = Carbon::parse($k->created_at ?? now());

        // Hitung durasi layanan
        $k->durasi_layanan = '-';
        if (!empty($k->waktu_mulai_layanan) && !empty($k->waktu_selesai_layanan)) {
            $waktuMulai = Carbon::parse($k->waktu_mulai_layanan);
            $waktuAkhir = Carbon::parse($k->waktu_selesai_layanan);

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
        'daftar_prodi' => $daftar_prodi, // Sekarang data dipastikan terisi & terkirim ke view
        'data_kunjungan' => $data_kunjungan,
        'judul_dashboard' => 'Manajemen Antrean'
    ]);
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

        // SET PRODI USER
        $user->prodi = $db['master_prodi_instansi']
            ->firstWhere('id',$user->prodi_id);

        // DAFTAR PRODI
        $daftar_prodi = $db['master_prodi_instansi']
            ->where('jenis','Prodi')
            ->values();

        // FILTER AKSES
        $query = $this->applyAccessFilter(
            $db['kunjungan'],
            $user
        );

        // FILTER PRODI
        if(request()->filled('prodi_id')){

            $query = $query
                ->where('prodi_id',request('prodi_id'))
                ->values();
        }

        // HANYA YANG ADA SURVEY
        $query = $query->filter(function($k) use ($db){

            return $db['survey']
                ->contains('kunjungan_id',$k->id);

        });

        // MAP DATA
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

            $k->created_at = Carbon::parse(
                $k->created_at ?? now()
            );

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

    public function mulaiProses(Request $request, $nomor_kunjungan)
    {
        $request->validate([
            'estimasi_sla' => 'required|integer|min:1',
            'satuan_sla' => 'required|in:Menit,Hari'
        ]);

        $kunjungan = $this->readSheet('kunjungan')->firstWhere('nomor_kunjungan', $nomor_kunjungan);
        if (!$kunjungan) return back()->with('error', 'Data tidak ditemukan.');

        $this->updateSheet('kunjungan', $kunjungan->id, [
            'status_layanan' => 'Diproses',
            'estimasi_sla' => $request->estimasi_sla,
            'satuan_sla' => $request->satuan_sla,
            'user_id' => $this->getSessionUser()->id ?? 0,

            // TAMBAHKAN BARIS INI UNTUK MENCATAT WAKTU MULAI LAYANAN
            'waktu_mulai_layanan' => Carbon::now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Antrean ' . $nomor_kunjungan . ' berhasil diproses.');
    }

    public function tolak(Request $request, $id)
    {
        $request->validate([
            'alasan_tolak' => 'required|string|max:255'
        ]);

        /*
        =========================================================
        STATUS DITOLAK
        =========================================================

        Sesuai rumus KPI:
        - Tepat Waktu = 1
        - Terlambat = 0.5
        - Ditolak = 0

        Jadi langsung simpan:
        - status_sla = DITOLAK
        - skor_pelayanan = 0
        =========================================================
        */

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
    // 1. Ambil data kunjungan dari sheet berdasarkan ID
    $kunjungan = $this->readSheet('kunjungan')->firstWhere('id', $id);
        
    if (!$kunjungan) {
        return back()->with('error', 'Data tidak ditemukan');
    }

    // 2. Validasi file, maksimal 4MB
    $request->validate([
        'file_surat' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg|max:4096'
    ]);

    if ($request->hasFile('file_surat')) {
        $file = $request->file('file_surat');
        $ekstensi = $file->getClientOriginalExtension();
        $namaFile = 'surat_' . str_replace('-', '_', $kunjungan->nomor_kunjungan) . '_' . time() . '.' . $ekstensi;

        // Ubah file menjadi string teks Base64
        $fileBase64 = base64_encode(file_get_contents($file->getRealPath()));

        $urlGas = 'https://script.google.com/macros/s/AKfycbz6QBns1Z3Sh1lhA5tgAJTOLL0sIdrTaudgNoSBitz3PrfCzH80vE36vMLkxTc10Lc1/exec';

        try {
            // PERBAIKAN UTAMA: Kirim sebagai JSON murni dan selipkan 'action' di dalam body JSON!
            // Sesuai dengan kemauan baris kode GAS kamu: JSON.parse(e.postData.contents)
            $response = Http::post($urlGas . '?action=upload_file', [
                'id'          => $id,
                'nama_file'   => $namaFile,
                'tipe_mime'   => $file->getMimeType(),
                'file_base64' => $fileBase64
            ]);

            $hasil = $response->json();

            // Jika GAS sukses memproses dan mengembalikan link Drive
            if (isset($hasil['status']) && $hasil['status'] === 'success') {
                
                // Simpan LINK GOOGLE DRIVE tersebut ke Google Sheets
                $this->updateSheet('kunjungan', $kunjungan->id, [
                    'file_surat' => $hasil['link']
                ]);

                return back()->with(
                    'success_upload_remind',
                    'Berkas pendukung berhasil diunggah secara permanen ke Google Drive prodi!'
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
        // =========================================================
        // HITUNG SLA
        // =========================================================
        $estimasi = (int) ($kunjungan->estimasi_sla ?? 30);
        $satuan = $kunjungan->satuan_sla ?? 'Menit';

        $waktuMulai = !empty($kunjungan->waktu_mulai_layanan)
            ? Carbon::parse($kunjungan->waktu_mulai_layanan)
            : Carbon::parse($kunjungan->created_at);
        $batasWaktu = $waktuMulai->copy();
        if ($satuan == 'Hari') {
            $batasWaktu->addDays($estimasi);
        } else {
            $batasWaktu->addMinutes($estimasi);
        }
        // =========================================================
        // STATUS SLA
        // =========================================================
        if ($waktuSelesai->greaterThan($batasWaktu)) {
            $statusSla = 'TERLAMBAT';
            $skorPelayanan = 0.5;
        } else {
            $statusSla = 'TEPAT WAKTU';
            $skorPelayanan = 1;
        }
        // =========================================================
        // UPDATE DATA
        // =========================================================
        $this->updateSheet('kunjungan', $kunjungan->id, [
            'status_layanan' => 'Selesai',
            'user_id' => $this->getSessionUser()->id ?? 0,
            'waktu_selesai_layanan' =>
                $waktuSelesai->toDateTimeString(),
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
            'email_pimpinan' => 'required|email'
        ]);

        $db = $this->readMultipleSheets(['kunjungan', 'pengunjung', 'master_prodi_instansi', 'master_keperluan']);

        // 1. Ambil data kunjungan berdasarkan ID (Gaya asli kamu yang aman)
        $kunjungan = $db['kunjungan']->first(function($item) use ($request) {
            return isset($item->id) && $item->id == $request->kunjungan_id;
        });

        if (!$kunjungan) return back()->with('error', 'Kunjungan tidak ditemukan');
        $kunjungan = (object) $kunjungan;

        // 2. Ambil data pengunjung terkait
        $pengunjungData = $db['pengunjung']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == $kunjungan->pengunjung_id;
        });

        if ($pengunjungData) {
            $kunjungan->pengunjung = (object) $pengunjungData;
            $kunjungan->pengunjung->instansi = $kunjungan->pengunjung->asal_instansi ?? 'Umum / Mandiri';
        } else {
            $kunjungan->pengunjung = (object) ['nama_lengkap' => 'Umum', 'instansi' => 'Umum / Mandiri'];
        }

        // 3. Ambil data keperluan
        $masterKeperluan = $db['master_keperluan']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == ($kunjungan->keperluan_id ?? null);
        });
        $kunjungan->nama_keperluan_utama = $masterKeperluan->keterangan ?? 'Kunjungan Umum';
        $kunjungan->keperluan_detail = !empty($kunjungan->keperluan) ? $kunjungan->keperluan : '-';

// 4. Ambil data prodi terkait
        $prodiData = $db['master_prodi_instansi']->first(function($item) use ($kunjungan) {
            return isset($item->id) && $item->id == ($kunjungan->prodi_id ?? null);
        });
        
        $namaProdi = '-';
        if ($prodiData) {
            $prodiData = (object) $prodiData;
            // DIUBAH DI SINI: tambahkan $prodiData->nama di urutan paling depan
            $namaProdi = $prodiData->nama ?? $prodiData->nama_prodi ?? $prodiData->prodi ?? '-';
        }

        // BIAR BLADE LOKAL BISA MEMBACA PRODI
        $kunjungan->nama_prodi = $namaProdi;

        // 5. PROSES TEMBAK KE GOOGLE SCRIPT (Menerobos Firewall SMTP Vercel)
        try {
            $urlGas = $this->getApiUrl(); // Otomatis mengambil dari env GOOGLE_SCRIPT_URL kamu

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
                return back()->with('success', 'Email berhasil diteruskan ke pimpinan via Google Server!');
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

        // Membaca seluruh sheet master dari Google Sheets
        $db = $this->readMultipleSheets(['master_user', 'master_keperluan', 'master_role', 'master_prodi_instansi']);

        // Membuat peta referensi (Key-Value) untuk Role dan Prodi berbasis ID agar mapping cepat
        $rolesMap = collect($db['master_role'])->keyBy('id')->toArray();
        $prodiMap = collect($db['master_prodi_instansi'])->keyBy('id')->toArray();

        // Gabungkan teks nama_role dan nama_prodi ke dalam data user
        $mappedUsers = collect($db['master_user'])->map(function ($u) use ($rolesMap, $prodiMap) {
            $roleId = data_get($u, 'role_id');
            $prodiId = data_get($u, 'prodi_id');

            // 1. Suntikkan teks Role
            $u->nama_role = isset($rolesMap[$roleId]) ? data_get($rolesMap[$roleId], 'nama_role') : 'Tanpa Role';

            // 2. Suntikkan teks Prodi (Cerdas: Cek apakah prodi_id berupa ID Angka atau Teks Lama)
            if (isset($prodiMap[$prodiId])) {
                // Jika berupa ID angka yang cocok dengan master_prodi
                $u->nama_prodi = data_get($prodiMap[$prodiId], 'nama');
            } else {
                // Jika berupa teks nama langsung (untuk data lama Anda seperti 'Teknik Informatika') atau kosong
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
            'prodiRaw' => $db['master_prodi_instansi'] // Dikirim untuk modal dropdown
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
            'prodi_id' => 'nullable' // Menerima ID angka prodi
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
            'prodi_id'   => $request->prodi_id ?? '', // ID angka tersimpan di sini
            'name'       => $request->name,
            'email'      => $request->email,
            'password'   => $request->password,
            'foto'       => '',
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
            'prodi_id' => 'nullable' // Menerima ID angka prodi
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
                'prodi_id'   => $request->prodi_id ?? '', // Update ID angka ke baris lama
                'name'       => $request->name,
                'email'      => $request->email,
                'password'   => $password,
                'foto'       => data_get($existingUser, 'foto') ?? '',
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
        $request->validate(['keterangan' => 'required|string|max:255']);

        $this->createSheet('master_keperluan', [
            'keterangan' => $request->keterangan
        ]);

        return back()->with('success', 'Keperluan baru berhasil ditambahkan.');
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
