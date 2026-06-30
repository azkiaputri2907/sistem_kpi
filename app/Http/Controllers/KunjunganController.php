<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Mail\NotifikasiPimpinanMail;

class KunjunganController extends Controller
{
    // =========================================================================
    // HELPER FUNCTIONS UNTUK GOOGLE SPREADSHEET API
    // =========================================================================

    private function getApiUrl()
    {
        return env('GOOGLE_SCRIPT_URL');
    }

    private function readSheet($sheetName)
    {
        try {
            $response = Http::get($this->getApiUrl(), [
                'action' => 'read',
                'sheet'  => $sheetName
            ]);

            // Jika Google malah mengembalikan HTML / Error
            if (!$response->successful() || !is_array($response->json('data'))) {
                \Illuminate\Support\Facades\Log::error("API Error / Bukan JSON di sheet: " . $sheetName);
                return collect([]); // Kembalikan array kosong agar tidak error
            }

            $data = $response->json('data') ?? [];
            return collect(json_decode(json_encode($data), FALSE));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal baca sheet $sheetName: " . $e->getMessage());
            return collect([]);
        }
    }

    private function createSheet($sheetName, $data)
    {
        $response = Http::post($this->getApiUrl() . '?action=create&sheet=' . $sheetName, $data);
        return $response->json() ?? ['inserted_id' => rand(1000, 9999)]; // Fallback jika gagal
    }

    private function updateSheet($sheetName, $id, $data)
    {
        $data['id'] = $id;
        $response = Http::post($this->getApiUrl() . '?action=update&sheet=' . $sheetName, $data);
        return $response->json() ?? [];
    }

    // =========================================================================
    // CONTROLLER LOGIC
    // =========================================================================

    public function create()
    {
        // 1. Ambil data Prodi
        $semuaProdi = $this->readSheet('master_prodi_instansi');
        $prodi = $semuaProdi->where('jenis', 'Prodi')->values();

        // 2. Ambil data Keperluan (Grouping manual seperti DB::raw MIN(id))
        $semuaKeperluan = $this->readSheet('master_keperluan');
        $keperluan = $semuaKeperluan->unique('keterangan')->values();

        return view('landing', compact('prodi', 'keperluan'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_lengkap'=>'required|string|max:50',
            'no_telepon'=>'required|string|max:15',
            'asal_instansi'=>'required|string|max:50',
            'prodi_id'=>'required',
            'keperluan_id'=>'required'
        ],[
            'nama_lengkap.required'=>'Nama lengkap wajib diisi',
            'no_telepon.required'=>'No WhatsApp wajib diisi',
            'asal_instansi.required'=>'Asal instansi wajib diisi',
            'prodi_id.required'=>'Tujuan prodi wajib dipilih',
            'keperluan_id.required'=>'Kategori keperluan wajib dipilih'
        ]);

        try {
            // 2. Cari atau Buat Pengunjung
            $pengunjungList = $this->readSheet('pengunjung');
            $pengunjung = $pengunjungList->firstWhere('no_telepon', $request->no_telepon);

            $pengunjungId = null;
            if (!$pengunjung) {
                $baru = $this->createSheet('pengunjung', [
                    'nama_lengkap' => $request->nama_lengkap,
                    'no_telepon'   => $request->no_telepon,
                    'identitas_no' => $request->identitas_no,
                    'asal_instansi'=> $request->asal_instansi
                ]);

                // Pengecekan aman agar tidak error jika API Google gagal membalas ID
                $pengunjungId = is_array($baru) && isset($baru['inserted_id']) ? $baru['inserted_id'] : rand(1000, 9999);

                $pengunjung = (object) [
                    'id' => $pengunjungId,
                    'nama_lengkap' => $request->nama_lengkap,
                    'asal_instansi' => $request->asal_instansi
                ];
            } else {
                $pengunjungId = $pengunjung->id;
            }

            // 3. Catatan Keperluan Tambahan
            $nomor_kunjungan = 'IN-' . date('ymd') . '-' . rand(100, 999);

            $kunjunganData = [
                'nomor_kunjungan' => $nomor_kunjungan,
                'pengunjung_id'   => $pengunjungId,
                'prodi_id'        => $request->prodi_id,
                'keperluan_id'    => $request->keperluan_id,
                'keperluan'       => $request->catatan_keperluan ?? '-',
                'hari_kunjungan'  => Carbon::now()->locale('id')->isoFormat('dddd'),
                'tanggal'         => Carbon::now()->toDateString(),
                'status_layanan'  => 'Antre',
                'status_pimpinan' => 'Menunggu',
            ];

            // 4. Kirim data ke Spreadsheet Kunjungan
            $createKunjungan = $this->createSheet('kunjungan', $kunjunganData);

            // 5. Bypass (Lewati) proses Email agar tidak mengganggu perpindahan halaman
            try {
                $kunjunganObj = (object) $kunjunganData;
                $kunjunganObj->id = is_array($createKunjungan) && isset($createKunjungan['inserted_id']) ? $createKunjungan['inserted_id'] : rand(1000, 9999);
                $kunjunganObj->pengunjung = $pengunjung;

                $semuaUser = $this->readSheet('master_user');
                $pimpinan = $semuaUser->filter(function($u) use ($request) {
                    return ($u->role_id == 4 && $u->prodi_id == $request->prodi_id) || ($u->role_id == 3);
                });

                foreach ($pimpinan as $user) {
                    Mail::send('emails.notifikasi_kunjungan', ['kunjungan' => $kunjunganObj, 'url_login' => url('/login')], function($message) use ($user) {
                        $message->to($user->email)->subject('Notifikasi Antrean Baru');
                    });
                }
            } catch (\Exception $e) {
                // Biarkan saja jika email gagal, pendaftaran tetap sukses
                Log::warning("Email pimpinan gagal: " . $e->getMessage());
            }

            // 6. REDIRECT PAKSA KE HALAMAN PROSES MENGGUNAKAN URL LANGSUNG
            return redirect('/status/' . $nomor_kunjungan)->with('success', 'Pendaftaran antrean berhasil!');

        } catch (\Exception $e) {
            Log::error("Proses pendaftaran gagal: " . $e->getMessage());

            // Tampilkan error paksa di layar jika ternyata API Google-nya yang bermasalah
            dd("Error sistem: " . $e->getMessage() . ". Mohon periksa API Spreadsheet Anda.");
        }
    }

    public function cekStatus($id)
    {
        $kunjunganList = $this->readSheet('kunjungan');
        $kunjungan = $kunjunganList->firstWhere('nomor_kunjungan', $id);

        if (!$kunjungan) abort(404, 'Kunjungan tidak ditemukan');

        // 1. Tempelkan data Pengunjung secara manual
        $pengunjungList = $this->readSheet('pengunjung');
        $kunjungan->pengunjung = $pengunjungList->firstWhere('id', $kunjungan->pengunjung_id);

        // 2. Tempelkan data Keperluan Master secara manual
        $keperluanList = $this->readSheet('master_keperluan');
        $kunjungan->keperluan_master = $keperluanList->firstWhere('id', $kunjungan->keperluan_id);

        // 3. Cek apakah ada survey
        $surveyList = $this->readSheet('survey');
        $kunjungan->survey = $surveyList->firstWhere('kunjungan_id', $kunjungan->id);

        // 4. Ubah format waktu (String) menjadi Objek Carbon agar fungsi ->format() di Blade tidak error
        $kunjungan->created_at = Carbon::parse($kunjungan->created_at ?? now());
        $kunjungan->updated_at = Carbon::parse($kunjungan->updated_at ?? now());

        $durasi_menit = 0;
        if (!empty($kunjungan->waktu_selesai_layanan)) {
            $created = Carbon::parse($kunjungan->created_at);
            $selesai = Carbon::parse($kunjungan->waktu_selesai_layanan);
            $durasi_menit = round($created->diffInMinutes($selesai));
        }

        return view('proses', compact('kunjungan', 'durasi_menit'));
    }

    public function formSurvey($nomor_kunjungan)
    {
        $kunjunganList = $this->readSheet('kunjungan');
        $kunjungan = $kunjunganList->firstWhere('nomor_kunjungan', $nomor_kunjungan);

        if (!$kunjungan) abort(404);

        $surveyList = $this->readSheet('survey');
        if ($surveyList->firstWhere('kunjungan_id', $kunjungan->id)) {
            return redirect()->route('kunjungan.status', ['id' => $nomor_kunjungan])
                            ->with('error', 'Anda sudah mengisi survey untuk antrean ini.');
        }

        // Ambil data pengunjung
        $pengunjungList = $this->readSheet('pengunjung');
        $pengunjung = $pengunjungList->firstWhere('id', $kunjungan->pengunjung_id);

        $nama_tamu = $pengunjung->nama_lengkap ?? session('nama_tamu', 'Tamu');

        // Ambil aspek & pertanyaan
        $aspek_survey = $this->readSheet('master_aspek_survey');
        $pertanyaanList = $this->readSheet('master_pertanyaan');

        foreach ($aspek_survey as $aspek) {
            $aspek->pertanyaan = $pertanyaanList->where('aspek_id', $aspek->id)->values();
        }

        $durasi = 'Belum selesai';

        if (
            !empty($kunjungan->waktu_mulai_layanan) &&
            !empty($kunjungan->waktu_selesai_layanan)
        ) {

            // Waktu mulai dari admin klik "Proses"
            $waktuMulai = Carbon::parse($kunjungan->waktu_mulai_layanan);

            // Waktu selesai layanan
            $waktuAkhir = Carbon::parse($kunjungan->waktu_selesai_layanan);

            $totalDetik = $waktuMulai->diffInSeconds($waktuAkhir);

            $jam = floor($totalDetik / 3600);
            $menit = floor(($totalDetik % 3600) / 60);
            $detik = $totalDetik % 60;

            if ($jam > 0) {
                $durasi = "{$jam} Jam {$menit} Mnt";
            } elseif ($menit > 0) {
                $durasi = "{$menit} Mnt {$detik} Dtk";
            } else {
                $durasi = "{$detik} Detik";
            }
        }

        return view('guest.form-survey', compact(
            'kunjungan',
            'aspek_survey',
            'nama_tamu',
            'durasi'
        ));
    }

public function storeSurvey(Request $request)
{
    $request->validate([
        'nomor_kunjungan' => 'required',
        'jawaban' => 'required|array',
        'catatan' => 'nullable|string',
    ]);

    $kunjunganList = $this->readSheet('kunjungan');
    $kunjungan = $kunjunganList->firstWhere('nomor_kunjungan', $request->nomor_kunjungan);

    if (!$kunjungan) {
        return back()->with('error', 'Data kunjungan tidak ditemukan.');
    }

    $p1 = $request->jawaban[1] ?? 0;
    $p2 = $request->jawaban[2] ?? 0;
    $p3 = $request->jawaban[3] ?? 0;
    $p4 = $request->jawaban[4] ?? 0;
    $p5 = $request->jawaban[5] ?? 0;

    $skor_total_y = ($p1 + $p2 + $p3 + $p4 + $p5) * 4;

    // 1. Simpan ke sheet 'survey'
    $simpanSurvey = $this->createSheet('survey', [
        'kunjungan_id' => $kunjungan->id,
        'kritik_saran' => $request->catatan,
        'skor_total'   => $skor_total_y
    ]);

    // 2. Proteksi Penguncian ID: Jika API telat membalas, baca sheet untuk mengambil ID riil terakhir
    $surveyId = null;
    if (is_array($simpanSurvey) && isset($simpanSurvey['inserted_id'])) {
        $surveyId = $simpanSurvey['inserted_id'];
    } else {
        $bacaUlangSurvey = $this->readSheet('survey');
        $surveyTerakhir = $bacaUlangSurvey->where('kunjungan_id', $kunjungan->id)->last();
        $surveyId = $surveyTerakhir ? $surveyTerakhir->id : rand(1, 99); 
    }

    // 3. Simpan Detail Survey menggunakan ID yang valid
    $this->createSheet('detail_survey', [
        'survey_id' => $surveyId,
        'p1' => $p1,
        'p2' => $p2,
        'p3' => $p3,
        'p4' => $p4,
        'p5' => $p5,
    ]);

    return back()->with('success', 'Terima kasih atas ulasan Anda!');
}

public function kirimMassal(Request $request)
{
    $ids = $request->ids;
    $tujuan = $request->tujuan_pimpinan;

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $statusTujuan = $tujuan == 'kajur' ? 'Menunggu Kajur' : 'Menunggu Kaprodi';

    // Google Spreadsheet tidak mendukung bulk update, kita harus loop satu-satu
    foreach ($ids as $id) {
        $this->updateSheet('kunjungan', $id, [
            'is_forwarded'    => 1,
            'tujuan_pimpinan' => $tujuan,
            'status_pimpinan' => $statusTujuan
        ]);
    }

    $namaTujuan = $tujuan == 'kajur' ? 'Kajur' : 'Kaprodi';

    // Ambil ID pertama dari array ids karena alur otomatis ini dipicu dari tombol baris tunggal
    $kunjunganId = $ids[0] ?? null;

    // Kembalikan back dengan tambahan data Flash Session untuk memicu modal email di JavaScript
    return back()->with([
        'success' => count($ids) . " data berhasil diteruskan ke $namaTujuan.",
        'trigger_email_modal' => true,
        'email_kunjungan_id'  => $kunjunganId,
        'email_nama'          => $request->input('nama_pengunjung', 'Umum'),
        'email_keperluan'     => $request->input('keperluan_pengunjung', '-')
    ]);
}

    public function cekPengunjung($identitas)
    {
        $pengunjungList = $this->readSheet('pengunjung');

        $pengunjung = $pengunjungList->firstWhere('identitas_no', $identitas);

        if($pengunjung){

            return response()->json([
                'status' => 'found',
                'data' => $pengunjung
            ]);
        }

        return response()->json([
            'status' => 'not_found'
        ]);
    }
public function getAntreanDiproses()
{
    try {
        // Pastikan fungsi readSheet di controller ini bekerja dengan benar
        $kunjunganList = $this->readSheet('kunjungan');

        if (!$kunjunganList || $kunjunganList->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        // Ambil SEMUA data tanpa filter tanggal hari ini
        $antreanAktif = $kunjunganList->filter(function($item) {
            // Ambil status_layanan secara aman dari objek
            $status = isset($item->status_layanan) ? trim($item->status_layanan) : '';

            // Ambil semua yang berstatus "Diproses" (tidak peduli tanggal berapa)
            return strtolower($status) === 'diproses';
        })->map(function($item) {
            return [
                'nomor' => $item->nomor_kunjungan ?? '',
                'nomor_kunjungan' => $item->nomor_kunjungan ?? '', // kita sediakan dua versi agar JavaScript tipe apapun bisa membaca
                'status_layanan' => $item->status_layanan ?? 'Diproses'
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $antreanAktif
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}
}
