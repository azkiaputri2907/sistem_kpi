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
        try {
            $response = Http::post($this->getApiUrl() . '?action=create&sheet=' . $sheetName, $data);
            
            // Jika sukses dan berbentuk array JSON, langsung kembalikan responnya
            if ($response->successful() && is_array($response->json())) {
                return $response->json();
            }
            
            // Jika gagal/timeout, kembalikan array kosong (jangan pakai rand() lagi!)
            \Illuminate\Support\Facades\Log::warning("API Create gagal atau timeout di sheet: " . $sheetName);
            return [];
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Exception pada createSheet $sheetName: " . $e->getMessage());
            return [];
        }
    }

    private function updateSheet($sheetName, $id, $data)
    {
        try {
            $data['id'] = $id;
            $response = Http::post($this->getApiUrl() . '?action=update&sheet=' . $sheetName, $data);
            
            if ($response->successful() && is_array($response->json())) {
                return $response->json();
            }
            
            \Illuminate\Support\Facades\Log::warning("API Update gagal di sheet: " . $sheetName);
            return [];
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Exception pada updateSheet $sheetName: " . $e->getMessage());
            return [];
        }
    }

    // =========================================================================
    // HELPER JADWAL OPERASIONAL & LIMIT
    // =========================================================================
    private function cekSistemBuka()
    {
        // Pengecekan zona waktu WITA (Banjarmasin)
        $sekarang = Carbon::now('Asia/Makassar');
        $hari = $sekarang->dayOfWeekIso; // 1 = Senin, 5 = Jumat, 7 = Minggu
        $jam = $sekarang->format('H:i');

        // Aturan Jam Operasional & Istirahat (Dalam WITA)
        $bukaHari     = ($hari >= 1 && $hari <= 5); // Hanya Senin - Jumat
        $jamKerjaPagi = ($jam >= '08:00' && $jam < '12:00');
        $jamIstirahat = ($jam >= '12:00' && $jam < '13:00'); // Waktu Istirahat
        $jamKerjaSore = ($jam >= '13:00' && $jam <= '16:00');

        // Status utama: Buka HANYA di hari kerja DAN (jam pagi ATAU jam sore)
        $statusBuka = $bukaHari && ($jamKerjaPagi || $jamKerjaSore);

        // Menentukan pesan & waktu buka selanjutnya (untuk fitur hitung mundur / countdown)
        $pesan = 'Buka';
        $waktuBukaSelanjutnya = null;

        if (!$bukaHari) {
            $pesan = 'Sistem libur akhir pekan. Buka kembali hari Senin pukul 08:00 WITA.';
            // Target buka: Senin berikutnya jam 08:00
            $waktuBukaSelanjutnya = $sekarang->copy()->next(Carbon::MONDAY)->setTime(8, 0)->toIso8601String();
        } elseif ($jam < '08:00') {
            $pesan = 'Sistem belum buka. Pelayanan dimulai pukul 08:00 WITA.';
            // Target buka: Hari ini jam 08:00
            $waktuBukaSelanjutnya = $sekarang->copy()->setTime(8, 0)->toIso8601String();
        } elseif ($jamIstirahat) {
            $pesan = 'Sistem sedang tutup untuk ISTIRAHAT. Buka kembali pukul 13:00 WITA.';
            // Target buka: Hari ini jam 13:00 setelah istirahat selesai
            $waktuBukaSelanjutnya = $sekarang->copy()->setTime(13, 0)->toIso8601String();
        } elseif ($jam > '16:00') {
            $pesan = 'Pelayanan hari ini telah selesai (Tutup pukul 16:00 WITA).';
            // Target buka: Besok jam 08:00 (Jika hari Jumat, lompat ke Senin)
            if ($hari == 5) {
                $waktuBukaSelanjutnya = $sekarang->copy()->next(Carbon::MONDAY)->setTime(8, 0)->toIso8601String();
            } else {
                $waktuBukaSelanjutnya = $sekarang->copy()->addDay()->setTime(8, 0)->toIso8601String();
            }
        }

        return [
            'status'       => $statusBuka,
            'pesan'        => $pesan,
            'is_istirahat' => $jamIstirahat,
            'target_buka'  => $waktuBukaSelanjutnya
        ];
    }

public function cekLimitAntreanPerProdi($prodi_id)
{
    // 1. Ambil data mentah
    $semuaKunjungan = $this->readSheet('kunjungan');

    // 2. Lakukan Filter yang ketat
    $jumlah = $semuaKunjungan->filter(function($item) use ($prodi_id) {
        // Kita bandingkan secara eksplisit
        $prodi_item = (string) ($item->prodi_id ?? '');
        $target = (string) $prodi_id;
        $status = strtolower(trim($item->status_layanan ?? ''));

        // Pastikan prodi cocok DAN statusnya benar-benar "antre"
        return ($prodi_item === $target) && ($status === 'antre');
    })->count();

    // 3. DEBUGGING: Uncomment baris di bawah ini jika masih bermasalah
    // Log::info("Cek Limit Prodi $prodi_id: Ditemukan $jumlah antrean aktif.");

    return $jumlah;
}

    // =========================================================================
    // CONTROLLER LOGIC UPDATES
    // =========================================================================

public function create()
{
    // 1. Cek Status Jadwal
    $statusOperasional = $this->cekSistemBuka();

    // 2. Ambil semua data kunjungan
    $semuaKunjungan = $this->readSheet('kunjungan');

    // 3. Ambil data Prodi dan hitung jumlah antrean tiap prodi secara real-time
    $semuaProdi = $this->readSheet('master_prodi_instansi');
    $prodi = $semuaProdi->where('jenis', 'Prodi')->map(function ($p) use ($semuaKunjungan) {
        $jumlahAntrean = $semuaKunjungan->where('prodi_id', (string)$p->id)
                                        ->where('status_layanan', 'Antre')
                                        ->count();
        
        $p->jumlah_antrean = $jumlahAntrean;
        $p->is_full = ($jumlahAntrean >= 10); 
        return $p;
    })->values();

    // 4. Ambil data Keperluan
    $semuaKeperluan = $this->readSheet('master_keperluan');
    $keperluan = $semuaKeperluan->unique('keterangan')->values();

    // FIX UTAMA: Tambahkan 'semuaKunjungan' ke dalam compact agar bisa dibaca oleh Blade landing page
    return view('landing', compact('prodi', 'keperluan', 'statusOperasional', 'semuaKunjungan'));
}

public function getAntreanDiproses()
{
    try {
        $kunjunganList = $this->readSheet('kunjungan');
        $prodiList = $this->readSheet('master_prodi_instansi');

        // Ambil semua data kunjungan yang berstatus "Diproses"
        $diproses = $kunjunganList->filter(function($item) {
            $status = isset($item->status_layanan) ? trim($item->status_layanan) : '';
            return strtolower($status) === 'diproses';
        });

        // FIX KEDUA: Grouping data berdasarkan prodi_id agar strukturnya sesuai dengan JavaScript Ticker di Landing Page
        $grouped = $diproses->groupBy('prodi_id');
        $dataFormatted = [];

        foreach ($grouped as $prodiId => $items) {
            $prodiObj = $prodiList->firstWhere('id', $prodiId);
            $prodiName = $prodiObj ? ($prodiObj->nama ?? $prodiObj->nama_prodi) : ($prodiId === 'LAINNYA' ? 'Bagian Lainnya' : 'Umum');
            
            $antreanNomors = [];
            foreach ($items as $item) {
                if (!empty($item->nomor_kunjungan)) {
                    $antreanNomors[] = $item->nomor_kunjungan;
                }
            }

            if (!empty($antreanNomors)) {
                $dataFormatted[] = [
                    'prodi' => $prodiName,
                    'antrean' => $antreanNomors // Mengembalikan array nomor antrean untuk fungsi .join() di JS
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => $dataFormatted
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'data' => []
        ], 500);
    }
}

public function store(Request $request)
{
    // 1. Validasi Keamanan
    $statusOperasional = $this->cekSistemBuka();
    if (!$statusOperasional['status']) {
        return back()->withErrors(['sistem_tutup' => 'Pendaftaran ditolak: ' . $statusOperasional['pesan']])->withInput();
    }

    // ===============================================================
    // 1.5. SISTEM CEK KUOTA (10 TOKEN AKTIF) EFEK DOMINO
    // ===============================================================
    $hariIni = \Carbon\Carbon::now('Asia/Makassar')->format('Y-m-d'); 

    // Tarik data tiket & survei hari ini
    $semuaKunjungan = $this->readSheet('kunjungan')
        ->filter(function($item) use ($hariIni) {
            return \Carbon\Carbon::parse($item->created_at ?? now(), 'Asia/Makassar')->format('Y-m-d') === $hariIni;
        });
    $semuaSurvei = $this->readSheet('survey');

    $tokenTerpakai = 0;
    foreach ($semuaKunjungan as $k) {
        if (strtoupper(trim($k->status_layanan ?? '')) != 'DITOLAK') {
            $hasSurvey = $semuaSurvei->contains('kunjungan_id', $k->id);
            
            // Menghitung tiket yang belum Selesai ATAU (Sudah Selesai TAPI belum disurvei)
            if (strtoupper(trim($k->status_layanan ?? '')) != 'SELESAI' || !$hasSurvey) {
                $tokenTerpakai++;
            }
        }
    }

    // JIKA TOKEN PENUH (>= 10), BLOKIR PENDAFTARAN!
    if ($tokenTerpakai >= 10) {
        return back()->withErrors([
            'limit_penuh' => 'Mohon maaf, kuota antrean saat ini penuh (10/10). Sistem sedang menunggu pengunjung sebelumnya menyelesaikan ulasan layanan agar token tiket kembali tersedia. Silakan coba beberapa saat lagi.'
        ])->withInput();
    }
    // ===============================================================

    // 2. Validasi Limit Antrean Per-Prodi
    $targetProdi = $request->prodi_id;
    $prodiList = $this->readSheet('master_prodi');
    $prodiObj = $prodiList->firstWhere('id', $targetProdi);
    
    $namaProdi = $prodiObj ? $prodiObj->nama : ($targetProdi === 'LAINNYA' ? 'Bagian Lainnya' : 'Umum');

    if ($targetProdi && $this->cekLimitAntreanPerProdi($targetProdi) >= 10) {
        return back()->withErrors([
            'limit_penuh' => "Mohon maaf, antrean untuk **" . $namaProdi . "** saat ini sedang penuh (Maksimal 10 tamu). Silakan coba beberapa saat lagi."
        ])->withInput();
    }

    // 3. Validasi Input
    $request->validate([
        'tipe_tamu'       => 'required|in:Internal,Eksternal',
        'nama_lengkap'    => 'required|string|max:50',
        'identitas_no'    => $request->tipe_tamu === 'Internal' ? 'required|string|max:30' : 'nullable|string|max:30',
        'no_telepon'      => 'required|regex:/^[0-9]{10,15}$/',
        'asal_instansi'   => 'required|string|max:50',
        'prodi_id'        => $request->tipe_tamu === 'Internal' ? 'required' : 'nullable',
        'prodi_lainnya'   => 'required_if:prodi_id,LAINNYA|nullable|string|max:100',
        'keperluan_id'    => $request->tipe_tamu === 'Internal' ? 'required' : 'nullable',
        'surat_disposisi' => ($request->tipe_tamu === 'Eksternal' || $request->prodi_id === 'LAINNYA') 
                             ? 'required|file|mimes:pdf,jpg,jpeg,png|max:4096' 
                             : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
    ], [
        'no_telepon.regex'         => 'No WhatsApp wajib berupa angka 10-15 digit',
        'prodi_lainnya.required_if'=> 'Mohon isi nama prodi/bagian tujuan',
        'surat_disposisi.required' => 'Wajib mengunggah surat disposisi (Untuk Tamu Eksternal / Bagian Lainnya)',
        'surat_disposisi.mimes'    => 'Format file harus PDF, JPG, atau PNG',
        'surat_disposisi.max'      => 'Ukuran file maksimal 4MB',
    ]);

    try {
        // 4. Proses Upload File ke Google Drive via Base64
        $pathFile = '-';
        if ($request->hasFile('surat_disposisi')) {
            $file = $request->file('surat_disposisi');
            $namaClean = preg_replace('/[^A-Za-z0-9_\-]/', '_', $file->getClientOriginalName());
            $namaFile = 'surat_' . time() . '_' . $namaClean;
            
            // Konversi ke base64
            $fileData = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();

            try {
                // SAMA PERSIS DENGAN PARAMETER DI UPLOADFILE(), PLUS TARGET KOLOM
                $responseUpload = \Illuminate\Support\Facades\Http::timeout(60)->post($this->getApiUrl() . '?action=upload_file', [
                    'action'       => 'upload_file',
                    'id'           => 0, 
                    'target_kolom' => 'surat_disposisi', // Kasih tahu GAS ini untuk surat disposisi
                    'nama_file'    => $namaFile,
                    'tipe_mime'    => $mimeType,
                    'file_base64'  => $fileData
                ]);

                $resUploadData = $responseUpload->json();

                if ($responseUpload->successful() && isset($resUploadData['status']) && $resUploadData['status'] === 'success') {
                    $pathFile = $resUploadData['link']; 
                } else {
                    $pesanError = $resUploadData['message'] ?? 'Respons Google Script tidak valid';
                    throw new \Exception($pesanError);
                }
            } catch (\Exception $e) {
                // FALLBACK: Jika API Drive error/timeout
                \Illuminate\Support\Facades\Log::warning("Upload Google Drive gagal, beralih ke penyimpanan lokal: " . $e->getMessage());
                $file->move(public_path('uploads/surat'), $namaFile);
                $pathFile = url('uploads/surat/' . $namaFile); 
            }
        }

        // 5. Normalisasi No Telepon
        $noTeleponInput = $request->no_telepon;
        if (str_starts_with($noTeleponInput, '+62')) { $noTeleponInput = substr($noTeleponInput, 3); }
        if (str_starts_with($noTeleponInput, '0')) { $noTeleponInput = substr($noTeleponInput, 1); }

        // 6. Cari atau Buat Pengunjung
        $pengunjungList = $this->readSheet('pengunjung');
        $pengunjungList = $pengunjungList->map(function ($item, $index) {
            $item->id = empty($item->id) ? (int)($index + 1) : (int)$item->id;
            return $item;
        });

        $pengunjung = $pengunjungList->first(function ($value) use ($request, $noTeleponInput) {
            if ($request->tipe_tamu === 'Internal') {
                return !empty($value->identitas_no) && trim($value->identitas_no) === trim($request->identitas_no);
            } else {
                return !empty($value->no_telepon) && trim($value->no_telepon) === trim($noTeleponInput) && strtolower(trim($value->nama_lengkap)) === strtolower(trim($request->nama_lengkap));
            }
        });

        $pengunjungId = $pengunjung ? $pengunjung->id : ($pengunjungList->max('id') ?? 0) + 1;

        if (!$pengunjung) {
            $this->createSheet('pengunjung', [
                'id'            => $pengunjungId,
                'identitas_no'  => $request->identitas_no ?? '-',
                'nama_lengkap'  => $request->nama_lengkap,
                'asal_instansi' => $request->asal_instansi,
                'no_telepon'    => $noTeleponInput,
                'tipe_tamu'     => $request->tipe_tamu,
                'created_at'    => \Carbon\Carbon::now('Asia/Makassar')->toDateTimeString(),
            ]);
        }

        // 7. Setup Data Kunjungan
        $prefix = ($request->tipe_tamu === 'Eksternal') ? 'EK-' : 'IN-';
        $nomor_kunjungan = $prefix . date('ymd') . '-' . rand(100, 999);
        
        $catatanAkhir = $request->catatan_keperluan ?? '-';
        if ($request->prodi_id === 'LAINNYA' && $request->filled('prodi_lainnya')) {
            $catatanAkhir = "[Tujuan Bagian: " . $request->prodi_lainnya . "] - " . $catatanAkhir;
        }

        $kunjunganData = [
            'nomor_kunjungan' => $nomor_kunjungan,
            'pengunjung_id'   => $pengunjungId,
            'tipe_tamu'       => $request->tipe_tamu,
            'prodi_id'        => $request->prodi_id ?? 'LAINNYA', 
            'keperluan_id'    => $request->keperluan_id ?? '-',
            'keperluan'       => $catatanAkhir,
            'surat_disposisi' => $pathFile,
            'hari_kunjungan'  => \Carbon\Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd'),
            'tanggal'         => \Carbon\Carbon::now('Asia/Makassar')->toDateString(),
            'status_layanan'  => 'Antre',
            'status_pimpinan' => 'Menunggu',
        ];

        // 8. Simpan ke sheet kunjungan & Redirect
        $this->createSheet('kunjungan', $kunjunganData);

        return redirect('/status/' . $nomor_kunjungan)->with('success', 'Pendaftaran antrean berhasil!');

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error("Proses pendaftaran gagal: " . $e->getMessage());
        return back()->withErrors(['error' => 'Gagal memproses data. Coba lagi nanti.'])->withInput();
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
}
