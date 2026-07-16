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
            'target_buka'  => $waktuBukaSelanjutnya // Dipakai untuk hitung mundur di UI
        ];
    }

    private function cekLimitAntrean()
    {
        $kunjunganList = $this->readSheet('kunjungan');
        // Hitung tamu yang sedang aktif (Antre atau Diproses)
        $aktif = $kunjunganList->filter(function($item) {
            $status = strtolower(trim($item->status_layanan ?? ''));
            return in_array($status, ['antre', 'diproses']);
        })->count();

        return $aktif;
    }

    // =========================================================================
    // CONTROLLER LOGIC UPDATES
    // =========================================================================

    public function create()
    {
        // 1. Cek Status Jadwal & Limit
        $statusOperasional = $this->cekSistemBuka();
        $jumlahAktif = $this->cekLimitAntrean();
        $isLocked = ($jumlahAktif >= 10);

        // 2. Ambil data Prodi
        $semuaProdi = $this->readSheet('master_prodi_instansi');
        $prodi = $semuaProdi->where('jenis', 'Prodi')->values();

        // 3. Ambil data Keperluan
        $semuaKeperluan = $this->readSheet('master_keperluan');
        $keperluan = $semuaKeperluan->unique('keterangan')->values();

        return view('landing', compact('prodi', 'keperluan', 'statusOperasional', 'isLocked', 'jumlahAktif'));
    }

public function store(Request $request)
{
    // 1. Validasi Keamanan (Back-End protection untuk Jam & Limit)
    $statusOperasional = $this->cekSistemBuka();
    if (!$statusOperasional['status']) {
        return back()->withErrors(['sistem_tutup' => 'Pendaftaran ditolak: ' . $statusOperasional['pesan']])->withInput();
    }

    if ($this->cekLimitAntrean() >= 10) {
        return back()->withErrors(['limit_penuh' => 'Mohon maaf, antrean saat ini sedang penuh. Silakan coba beberapa saat lagi.'])->withInput();
    }

    // 2. Validasi Input (Sudah ramah Tamu Eksternal)
    $request->validate([
        'tipe_tamu'      => 'required|in:Internal,Eksternal',
        'nama_lengkap'   => 'required|string|max:50',
        'identitas_no'   => $request->tipe_tamu === 'Internal' ? 'required|string|max:30' : 'nullable|string|max:30',
        'no_telepon'     => 'required|regex:/^[0-9]{10,15}$/',
        'asal_instansi'  => 'required|string|max:50',
        'prodi_id'       => $request->tipe_tamu === 'Internal' ? 'required' : 'nullable',
        'prodi_lainnya'  => 'required_if:prodi_id,LAINNYA|nullable|string|max:100',
        'keperluan_id'   => $request->tipe_tamu === 'Internal' ? 'required' : 'nullable',
        'file_surat'     => $request->tipe_tamu === 'Eksternal'
                            ? 'required|file|mimes:pdf,jpg,jpeg,png|max:2048'
                            : 'required_if:prodi_id,LAINNYA|nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
    ], [
        'no_telepon.regex'          => 'No WhatsApp wajib berupa angka 10-15 digit',
        'prodi_lainnya.required_if' => 'Mohon isi nama prodi/bagian tujuan',
        'file_surat.required'       => 'Wajib mengunggah surat disposisi untuk Tamu Eksternal',
        'file_surat.required_if'    => 'Wajib mengunggah surat disposisi jika memilih Lainnya',
        'file_surat.mimes'          => 'Format file harus PDF, JPG, atau PNG',
        'file_surat.max'            => 'Ukuran file maksimal 2MB',
    ]);

    try {
       // 3. Normalisasi Nomor Telepon (Agar sinkron dan tidak salah mendeteksi pengunjung)
        $noTeleponInput = $request->no_telepon;
        if (str_starts_with($noTeleponInput, '+62')) {
            $noTeleponInput = substr($noTeleponInput, 3);
        }
        if (str_starts_with($noTeleponInput, '0')) {
            $noTeleponInput = substr($noTeleponInput, 1);
        }

        // Cari atau Buat Pengunjung
        $pengunjungList = $this->readSheet('pengunjung');

        // PENGAMAN URUTAN: Jika ada ID lama yang kosong/null di sheet, isi sementara dengan nomor urutan barisnya
        $pengunjungList = $pengunjungList->map(function ($item, $index) {
            $item->id = empty($item->id) ? (int)($index + 1) : (int)$item->id;
            return $item;
        });

        // PENCARIAN AKURAT:
        // - Internal dicari berdasarkan NIM/NIP.
        // - Eksternal dicari berdasarkan No HP DAN Nama Lengkap (menghindari duplikasi jika 1 nomor dipakai beda orang).
        $pengunjung = $pengunjungList->first(function ($value) use ($request, $noTeleponInput) {
            if ($request->tipe_tamu === 'Internal') {
                return !empty($value->identitas_no) && trim($value->identitas_no) === trim($request->identitas_no);
            } else {
                return !empty($value->no_telepon) &&
                       trim($value->no_telepon) === trim($noTeleponInput) &&
                       strtolower(trim($value->nama_lengkap)) === strtolower(trim($request->nama_lengkap));
            }
        });

        $pengunjungId = null;
        if (!$pengunjung) {
            // SINKRONISASI ID URUT: ID baru berdasarkan ID maksimal + 1 agar tidak bentrok
            $maxId = $pengunjungList->max('id') ?? 0;
            $pengunjungId = $maxId + 1;

            // PASTIKAN DATA INI DITULIS KE GOOGLE SPREADSHEET 'pengunjung'
            $this->createSheet('pengunjung', [
                'id'            => $pengunjungId,
                'identitas_no'  => $request->identitas_no ?? '-', // Tamu eksternal diberi default minus jika kosong
                'nama_lengkap'  => $request->nama_lengkap,
                'asal_instansi' => $request->asal_instansi,
                'no_telepon'    => $noTeleponInput,
                'tipe_tamu'     => $request->tipe_tamu, // <--- Ini mengirim "Eksternal" ke sheet!
                'created_at'    => Carbon::now('Asia/Makassar')->toDateTimeString(),
                'updated_at'    => Carbon::now('Asia/Makassar')->toDateTimeString(),
            ]);
        } else {
            // Jika pengunjung sudah ada, kita gunakan ID-nya
            $pengunjungId = $pengunjung->id;
        }
        // 4. Proses Upload File (Jika ada)
        $pathFile = null;
        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $namaFile = 'surat_' . time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/surat'), $namaFile);
            $pathFile = 'uploads/surat/' . $namaFile;
        }

        // 5. Setup Data Kunjungan (LOGIKA PREFIX EK- & IN- DIMASUKKAN DI SINI)
        $prefix = ($request->tipe_tamu === 'Eksternal') ? 'EK-' : 'IN-';
        $nomor_kunjungan = $prefix . date('ymd') . '-' . rand(100, 999);

        $catatanAkhir = $request->catatan_keperluan ?? '-';

        // Default value jika kosong
        $prodiIdSimpan = $request->prodi_id ?? '-';
        $keperluanIdSimpan = $request->keperluan_id ?? '-';

        // Penyesuaian jika Tamu Eksternal atau Memilih Prodi LAINNYA
        if ($request->tipe_tamu === 'Eksternal' || $request->prodi_id === 'LAINNYA') {
            $prodiIdSimpan = '-';
            if ($request->tipe_tamu === 'Eksternal') {
                $keperluanIdSimpan = '-';
            }
            if ($request->filled('prodi_lainnya')) {
                $catatanAkhir = "[Tujuan Bagian: " . $request->prodi_lainnya . "] - " . $catatanAkhir;
            }
        }

        $kunjunganData = [
            'nomor_kunjungan' => $nomor_kunjungan,
            'pengunjung_id'   => $pengunjungId, // DISIMPAN SINKRON dengan ID pengunjung di atas
            'tipe_tamu'       => $request->tipe_tamu,
            'prodi_id'        => $prodiIdSimpan,
            'keperluan_id'    => $keperluanIdSimpan,
            'keperluan'       => $catatanAkhir,
            'file_surat'      => $pathFile ?? '-',
            'hari_kunjungan'  => Carbon::now('Asia/Makassar')->locale('id')->isoFormat('dddd'),
            'tanggal'         => Carbon::now('Asia/Makassar')->toDateString(),
            'status_layanan'  => 'Antre',
            'status_pimpinan' => 'Menunggu',
        ];

        // 6. Simpan ke Spreadsheet
        $createKunjungan = $this->createSheet('kunjungan', $kunjunganData);

        // 7. Notifikasi Email Pimpinan (Hanya dikirim jika internal prodi valid)
        if ($request->tipe_tamu === 'Internal' && $prodiIdSimpan !== '-') {
            try {
                $semuaUser = $this->readSheet('master_user');
                $pimpinan = $semuaUser->filter(function($u) use ($prodiIdSimpan) {
                    return ($u->role_id == 4 && $u->prodi_id == $prodiIdSimpan) || ($u->role_id == 3);
                });

                foreach ($pimpinan as $user) {
                    Mail::send('emails.notifikasi_kunjungan', [
                        'kunjungan' => (object) array_merge($kunjunganData, ['id' => rand(1000, 9999)]),
                        'url_login' => url('/login')
                    ], function($message) use ($user) {
                        $message->to($user->email)->subject('Notifikasi Antrean Baru');
                    });
                }
            } catch (\Exception $e) {
                Log::warning("Email pimpinan gagal terkirim: " . $e->getMessage());
            }
        }

        return redirect('/status/' . $nomor_kunjungan)->with('success', 'Pendaftaran antrean berhasil!');

    } catch (\Exception $e) {
        Log::error("Proses pendaftaran gagal: " . $e->getMessage());
        return back()->withErrors(['error' => 'Terjadi kesalahan sistem, silakan coba lagi nanti.'])->withInput();
    }
}

   public function getAntreanDiproses()
{
    try {
        $kunjunganList = $this->readSheet('kunjungan');
        $prodiList = $this->readSheet('master_prodi_instansi');

        // 1. Ambil semua antrean yang berstatus Antre atau Diproses
        $antreanAktif = $kunjunganList->filter(function($item) {
            $status = isset($item->status_layanan) ? strtolower(trim($item->status_layanan)) : '';
            return in_array($status, ['antre', 'diproses']);
        });

        // 2. Normalisasi prodi_id sebelum dikelompokkan agar '-' atau '0' atau kosong menyatu ke satu grup
        $antreanAktif = $antreanAktif->map(function($item) {
            $prodiId = isset($item->prodi_id) ? trim($item->prodi_id) : '-';
            // Jika prodi_id adalah '0' atau kosong, samakan menjadi '-'
            if ($prodiId === '0' || $prodiId === '') {
                $prodiId = '-';
            }
            $item->prodi_id_normalized = $prodiId;
            return $item;
        });

        // 3. Kelompokkan berdasarkan Prodi ID yang sudah dinormalisasi
        $grouped = $antreanAktif->groupBy('prodi_id_normalized')->map(function($items, $prodiId) use ($prodiList) {
            // Jika ID-nya adalah '-', otomatis langsung beri nama Bagian Umum
            if ($prodiId === '-') {
                $namaProdi = 'Bagian Umum / Lainnya';
            } else {
                $prodiObj = $prodiList->firstWhere('id', $prodiId);
                $namaProdi = $prodiObj ? $prodiObj->nama : 'Bagian Umum / Lainnya';
            }

            return [
                'prodi' => $namaProdi,
                'antrean' => $items->pluck('nomor_kunjungan')->values()
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $grouped
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'data' => []
        ], 500);
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

        // Simpan Survey (Dapatkan ID baru)
        $simpanSurvey = $this->createSheet('survey', [
            'kunjungan_id' => $kunjungan->id,
            'kritik_saran' => $request->catatan,
            'skor_total'   => $skor_total_y
        ]);

        $surveyId = $simpanSurvey['inserted_id'] ?? rand(1000, 9999); // Fallback jika API gagal return id

        // Simpan Detail Survey
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
