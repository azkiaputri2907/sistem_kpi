<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengunjung;
use App\Models\Kunjungan;
use App\Models\MasterProdiInstansi;
use App\Models\MasterKeperluan;
use App\Models\MasterAspekSurvey;
use App\Models\Survey;
use App\Models\DetailSurvey;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KunjunganController extends Controller
{
    public function create()
    {
        $prodi = MasterProdiInstansi::where('jenis', 'Prodi')->get();

        $keperluan = DB::table('master_keperluan')
                        ->select('keterangan', DB::raw('MIN(id) as id'))
                        ->groupBy('keterangan')
                        ->get();

        return view('landing', compact('prodi', 'keperluan'));
    }

    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'nama_lengkap' => 'required|string|max:50',
            'no_telepon' => 'required|string|max:15',
            'asal_instansi' => 'required|string|max:50',
            'prodi_id' => 'required|integer',
            'keperluan_id' => 'required|integer'
        ]);

        try {
            // 2. Gunakan Transaction agar data Pengunjung & Kunjungan masuk serentak
            $kunjungan = DB::transaction(function () use ($request) {

                // Simpan atau ambil pengunjung
                $pengunjung = Pengunjung::firstOrCreate(
                    ['no_telepon' => $request->no_telepon],
                    [
                        'nama_lengkap' => $request->nama_lengkap,
                        'identitas_no' => $request->identitas_no,
                        'asal_instansi' => $request->asal_instansi
                    ]
                );

                // Buat nomor antrean
                $nomor_kunjungan = 'IN-' . date('ymd') . '-' . rand(100, 999);

                // Simpan data Kunjungan
              // Simpan data Kunjungan dengan menyertakan status default pimpinan
        return Kunjungan::create([
            'nomor_kunjungan' => $nomor_kunjungan,
            'pengunjung_id'   => $pengunjung->id,
            'prodi_id'        => $request->prodi_id,
            'keperluan_id'    => $request->keperluan_id,
            'keperluan'       => $request->catatan_keperluan ?? '-',
            'hari_kunjungan'  => Carbon::now()->isoFormat('dddd'),
            'tanggal'         => Carbon::now()->toDateString(),
            'status_layanan'  => 'Antre',
            'status_pimpinan' => 'Menunggu', // Tambahkan ini untuk memenuhi kolom di DB Anda
        ]);
            });

            // 3. Logika Email (Ditaruh di luar Transaction agar jika email gagal, data DB tetap aman)
          // Bagian di KunjunganController
try {
    $pimpinan = User::where('prodi_id', $request->prodi_id)
                    ->orWhere('role_id', 3)
                    ->get();

    foreach ($pimpinan as $user) {
        $dataEmail = [
            'kunjungan' => $kunjungan,
            'url_login' => url('/login')
        ];

        // Ganti nama view di sini agar sesuai dengan file yang Anda buat
        Mail::send('emails.notifikasi_kunjungan', $dataEmail, function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Notifikasi Antrean Baru');
        });
    }
} catch (\Exception $e) {
    // Jika email gagal, hanya catat di log, jangan hentikan proses redirect sukses
    Log::warning("Email pimpinan gagal: " . $e->getMessage());
}

            // 4. Redirect sukses
            return redirect()->route('kunjungan.status', ['kunjungan' => $kunjungan->nomor_kunjungan])
                             ->with('success', 'Pendaftaran antrean berhasil!');

        } catch (\Exception $e) {
            Log::error("Proses pendaftaran gagal: " . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal mendaftar antrean. Silakan coba lagi.');
        }
    }

    // Fungsi lainnya (cekStatus, formSurvey, storeSurvey) tetap sama...
    public function cekStatus(Kunjungan $kunjungan)
    {
        $durasi_menit = 0;
        if ($kunjungan->waktu_selesai_layanan) {
            $durasi_menit = round($kunjungan->created_at->diffInMinutes($kunjungan->waktu_selesai_layanan));
        }

        return view('proses', compact('kunjungan', 'durasi_menit'));
    }

    public function formSurvey($id)
    {
        $kunjungan = Kunjungan::where('nomor_kunjungan', $id)->firstOrFail();
        $nama_tamu = $kunjungan->pengunjung->nama_lengkap ?? session('nama_tamu', 'Tamu');
        $aspek_survey = MasterAspekSurvey::with('pertanyaan')->get();

        $durasi_menit = 0;
        if ($kunjungan->waktu_selesai_layanan) {
            $durasi_menit = round($kunjungan->created_at->diffInMinutes($kunjungan->waktu_selesai_layanan));
        }

        return view('guest.form-survey', compact('kunjungan', 'aspek_survey', 'nama_tamu', 'durasi_menit'));
    }

    public function storeSurvey(Request $request)
    {
        $request->validate([
            'nomor_kunjungan' => 'required',
            'jawaban' => 'required|array',
            'catatan' => 'nullable|string',
        ]);

        $kunjungan = Kunjungan::where('nomor_kunjungan', $request->nomor_kunjungan)->firstOrFail();

        $survey = Survey::create([
            'kunjungan_id' => $kunjungan->id,
            'kritik_saran' => $request->catatan,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        DetailSurvey::create([
            'survey_id' => $survey->id,
            'p1' => $request->jawaban[1] ?? 0,
            'p2' => $request->jawaban[2] ?? 0,
            'p3' => $request->jawaban[3] ?? 0,
            'p4' => $request->jawaban[4] ?? 0,
            'p5' => $request->jawaban[5] ?? 0,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
