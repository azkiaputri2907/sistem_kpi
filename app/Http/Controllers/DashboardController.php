<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Kunjungan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifikasiPimpinanMail;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Helper Private Method untuk Filter Akses
     */
    private function applyAccessFilter($query, $user)
    {
        // Role 1 (Admin) atau Role 3 (Kajur) bisa melihat semua data
        if ($user->role_id == 1 || $user->role_id == 3) {
            return $query;
        }

        // Role 2 (Admin Prodi) hanya melihat data Prodinya sendiri
        if ($user->prodi_id) {
            $query->where('prodi_id', $user->prodi_id);
        } else {
             $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public function index()
    {
        $user = Auth::user();

        // Jika bukan Admin/Prodi, arahkan ke analytics
        if (!in_array($user->role_id, [1, 2])) {
            return $this->analytics();
        }

        $query = Kunjungan::with(['pengunjung', 'prodi'])->latest();
        $query = $this->applyAccessFilter($query, $user);

        $isGlobal = ($user->role_id == 1 || $user->email === 'kajur.elektro@poliban.ac.id');

        return view('dashboard.index', [
            'user' => $user,
            'isGlobal' => $isGlobal,
            'judul_dashboard' => 'Dashboard Utama',
            'data_kunjungan' => $query->get()
        ]);
    }

    public function analytics()
    {
        $user = Auth::user();
        $query = Kunjungan::query();
        $query = $this->applyAccessFilter($query, $user);

        $dataSurvey = (clone $query)->whereHas('survey.detail')->with('survey.detail')->get();
        $puas = 0; $cukup = 0; $kurang = 0;

        foreach ($dataSurvey as $kunjungan) {
            $detail = $kunjungan->survey->detail;
            $rataRata = ($detail->p1 + $detail->p2 + $detail->p3 + $detail->p4 + $detail->p5) / 5;
            if ($rataRata >= 4) { $puas++; }
            elseif ($rataRata >= 3) { $cukup++; }
            else { $kurang++; }
        }

        $totalCount = $dataSurvey->count();
        $persentasePuas = $totalCount > 0 ? round(($puas / $totalCount) * 100) : 0;

        $distribusi = (clone $query)->join('master_keperluan', 'kunjungan.keperluan_id', '=', 'master_keperluan.id')
            ->select('master_keperluan.keterangan as keperluan', DB::raw('count(*) as total'))
            ->groupBy('master_keperluan.keterangan')->get();

        $tujuhHariLalu = Carbon::today()->subDays(6);
        $dataSla = (clone $query)->whereDate('created_at', '>=', $tujuhHariLalu)
            ->whereNotNull('status_sla')
            ->selectRaw('DATE(created_at) as tanggal, status_sla, count(*) as total')
            ->groupByRaw('DATE(created_at), status_sla')->get();

        $label_sla = []; $data_tepat_waktu = []; $data_terlambat = [];

        for ($i = 0; $i < 7; $i++) {
            $date = Carbon::today()->subDays(6 - $i)->format('Y-m-d');
            $label_sla[] = Carbon::parse($date)->format('d M');
            $data_tepat_waktu[] = (int) $dataSla->where('tanggal', $date)->where('status_sla', 'Tepat Waktu')->sum('total');
            $data_terlambat[] = (int) $dataSla->where('tanggal', $date)->where('status_sla', 'Terlambat')->sum('total');
        }

        return view('dashboard.analytics', [
            'user' => $user,
            'judul_dashboard' => 'Analytics KPI',
            'skor_kepuasan' => ['puas' => $puas, 'cukup' => $cukup, 'kurang' => $kurang, 'persen' => $persentasePuas],
            'distribusi_label' => $distribusi->pluck('keperluan'),
            'distribusi_data' => $distribusi->pluck('total'),
            'label_sla' => $label_sla,
            'data_tepat_waktu' => $data_tepat_waktu,
            'data_terlambat' => $data_terlambat
        ]);
    }

    public function laporan(Request $request)
    {
        $user = Auth::user();
        $query = Kunjungan::query();
        $query = $this->applyAccessFilter($query, $user);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        if ($startDate && $endDate) {
            $query->whereDate('created_at', '>=', $startDate)->whereDate('created_at', '<=', $endDate);
        }

        $totalSelesai = (clone $query)->where('status_layanan', 'Selesai')->count();
        $totalKunjungan = (clone $query)->count();
        $totalDitolak = (clone $query)->where('status_layanan', 'Ditolak')->count();
        $tingkatPenolakan = $totalKunjungan > 0 ? round(($totalDitolak / $totalKunjungan) * 100, 1) : 0;

        $kunjunganSelesai = (clone $query)->where('status_layanan', 'Selesai')->whereNotNull('waktu_selesai_layanan')->get();
        $totalMenit = 0;
        foreach ($kunjunganSelesai as $k) {
            $totalMenit += $k->created_at->diffInMinutes($k->waktu_selesai_layanan);
        }
        $rataRataSla = $kunjunganSelesai->count() > 0 ? round($totalMenit / $kunjunganSelesai->count()) : 0;

        $grafikQuery = (clone $query)->where('status_layanan', 'Selesai');
        if (!$startDate || !$endDate) {
            $grafikQuery->whereBetween('waktu_selesai_layanan', [now()->startOfWeek(), now()->endOfWeek()]);
        }

        $dataGrafikRaw = $grafikQuery->selectRaw('DAYOFWEEK(waktu_selesai_layanan) as hari, count(*) as total')
            ->groupBy('hari')->get();

        $grafikKinerja = ['Sen' => 0, 'Sel' => 0, 'Rab' => 0, 'Kam' => 0, 'Jum' => 0, 'Sab' => 0, 'Min' => 0];
        $hariMap = [2 => 'Sen', 3 => 'Sel', 4 => 'Rab', 5 => 'Kam', 6 => 'Jum', 7 => 'Sab', 1 => 'Min'];

        foreach ($dataGrafikRaw as $data) {
            if(isset($hariMap[$data->hari])) {
                $grafikKinerja[$hariMap[$data->hari]] = $data->total;
            }
        }

        return view('dashboard.laporan', [
            'user' => $user,
            'judul_dashboard' => 'Laporan & Ekspor',
            'totalSelesai' => $totalSelesai,
            'tingkatPenolakan' => $tingkatPenolakan,
            'rataRataSla' => $rataRataSla,
            'labelGrafik' => array_keys($grafikKinerja),
            'dataGrafik' => array_values($grafikKinerja),
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }

    public function manajemenAntrean(Request $request)
    {
        $user = Auth::user();
        $query = Kunjungan::with(['pengunjung', 'prodi'])->latest();
        $query = $this->applyAccessFilter($query, $user);

        if ($request->has('search')) {
            $query->where('nomor_kunjungan', 'LIKE', "%{$request->search}%");
        }

        return view('dashboard.antrean', [
            'user' => $user,
            'data_kunjungan' => $query->get(),
            'judul_dashboard' => 'Manajemen Antrean'
        ]);
    }

    public function ulasanLayanan()
    {
        $user = Auth::user();
        $query = Kunjungan::with(['pengunjung', 'survey.detail', 'prodi']);
        $query = $this->applyAccessFilter($query, $user);

        return view('dashboard.ulasan', [
            'user' => $user,
            'data_ulasan' => $query->whereHas('survey')->latest()->get(),
            'judul_dashboard' => 'Ulasan Pengunjung'
        ]);
    }

    public function mulaiProses(Request $request, Kunjungan $kunjungan)
    {
        $request->validate([
            'estimasi_sla' => 'required|integer|min:1',
            'satuan_sla' => 'required|in:Menit,Hari'
        ]);

        $kunjungan->update([
            'status_layanan' => 'Diproses',
            'estimasi_sla' => $request->estimasi_sla,
            'satuan_sla' => $request->satuan_sla,
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Antrean ' . $kunjungan->nomor_kunjungan . ' berhasil diproses.');
    }

    public function tolak(Kunjungan $kunjungan)
    {
        $kunjungan->update([
            'status_layanan' => 'Ditolak',
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', 'Antrean ' . $kunjungan->nomor_kunjungan . ' telah ditolak.');
    }

    public function selesai(Kunjungan $kunjungan)
    {
        $waktuSelesai = Carbon::now();
        $statusSla = null;

        if ($kunjungan->estimasi_sla && $kunjungan->satuan_sla) {
            $batasWaktu = $kunjungan->created_at->copy();
            if ($kunjungan->satuan_sla == 'Hari') {
                $batasWaktu->addDays($kunjungan->estimasi_sla);
            } elseif ($kunjungan->satuan_sla == 'Menit') {
                $batasWaktu->addMinutes($kunjungan->estimasi_sla);
            }
            $statusSla = $waktuSelesai->lessThanOrEqualTo($batasWaktu) ? 'Tepat Waktu' : 'Terlambat';
        }

        $kunjungan->update([
            'status_layanan' => 'Selesai',
            'user_id' => Auth::id(),
            'waktu_selesai_layanan' => $waktuSelesai,
            'status_sla' => $statusSla
        ]);

        return back()->with('success', 'Antrean ' . $kunjungan->nomor_kunjungan . ' telah selesai.');
    }

    /**
     * FUNGSI KIRIM EMAIL
     */
    public function kirimEmailPimpinan(Request $request)
    {
        $request->validate([
            'kunjungan_id' => 'required|exists:kunjungan,id',
            'email_pimpinan' => 'required|email'
        ]);

        $kunjungan = Kunjungan::with(['pengunjung', 'prodi'])->findOrFail($request->kunjungan_id);

        try {
            // Pastikan Mailable NotifikasiPimpinanMail sudah tidak meminta role_name di constructornya
            Mail::to($request->email_pimpinan)->send(new NotifikasiPimpinanMail($kunjungan));

            return back()->with('success', 'Email berhasil diteruskan ke pimpinan.');
        } catch (\Exception $e) {
            // Jika error, cek apakah .env sudah MAIL_MAILER=log
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }

    public function controlPanel()
    {
        $user = Auth::user();
        if ($user->role_id != 1) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak');
        }

        return view('dashboard.control_panel', [
            'user' => $user,
            'judul_dashboard' => 'Sistem Control Panel',
            'data_users' => \App\Models\User::all(),
            'data_keperluan' => DB::table('master_keperluan')->get()
        ]);
    }

 
  public function tanggapanPimpinan(Request $request, $id)
    {
        // 1. Validasi input
        $request->validate([
            'status_pimpinan' => 'required|in:Disetujui,Ditolak',
            'catatan_pimpinan' => 'nullable|string'
        ]);

        // 2. Cari data berdasarkan ID atau nomor kunjungan
        // Jika parameter di rute menggunakan ID biasa:
        $kunjungan = Kunjungan::findOrFail($id);

        // CATATAN: Jika di sistemmu menggunakan pencarian berdasarkan 'nomor_kunjungan', ganti baris di atas menjadi:
        // $kunjungan = Kunjungan::where('nomor_kunjungan', $id)->firstOrFail();

        // 3. Simpan perubahan
        $kunjungan->status_pimpinan = $request->status_pimpinan;
        $kunjungan->catatan_pimpinan = $request->catatan_pimpinan;
        $kunjungan->save();

        // 4. Redirect kembali dengan notifikasi sukses
        return back()->with('success', 'Tanggapan berhasil disimpan!');
    }

    public function storeKeperluan(Request $request)
    {
        $request->validate(['keterangan' => 'required']);
        DB::table('master_keperluan')->insert([
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return back()->with('success', 'Keperluan berhasil ditambah');
    }
}
