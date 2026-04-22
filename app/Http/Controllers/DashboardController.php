<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Kunjungan;
use App\Models\User; // Ditambahkan agar storeUser tidak error
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash; // Ditambahkan untuk Bcrypt password
use App\Mail\NotifikasiPimpinanMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    /**
     * Helper Private Method untuk Filter Akses
     */
    private function applyAccessFilter($query, $user)
    {
        if ($user->role_id == 1 || $user->role_id == 3) {
            return $query;
        }

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

   public function selesai(Request $request, $id)
{
    $kunjungan = Kunjungan::where('id', $id)->orWhere('nomor_kunjungan', $id)->firstOrFail();

    $request->validate([
        'file_surat' => 'nullable|mimes:pdf|max:2048',
    ]);

    $waktuSelesai = Carbon::now();
    $estimasi = $kunjungan->estimasi_sla ?? 30;
    $satuan = $kunjungan->satuan_sla ?? 'Menit';

    $batasWaktu = $kunjungan->created_at->copy();
    if ($satuan == 'Hari') {
        $batasWaktu->addDays($estimasi);
    } else {
        $batasWaktu->addMinutes($estimasi);
    }

    // --- LOGIKA PERHITUNGAN SKOR ---
    $skorAwal = 1.0;
    $pengurang = 0.5;

    if ($waktuSelesai->greaterThan($batasWaktu)) {
        $statusSla = 'Terlambat';

        // Hitung jumlah keterlambatan sesuai satuan (Menit atau Hari)
        if ($satuan == 'Hari') {
            $jumlahTerlambat = $waktuSelesai->diffInDays($batasWaktu);
        } else {
            $jumlahTerlambat = $waktuSelesai->diffInMinutes($batasWaktu);
        }

        // Jika lewat sedikit saja, minimal dikurang 0.5 satu kali (max 1)
        $totalUnitTerlambat = max(1, $jumlahTerlambat);
        $skorPelayanan = $skorAwal - ($totalUnitTerlambat * $pengurang);
    } else {
        $statusSla = 'Tepat Waktu';
        $skorPelayanan = $skorAwal; // Tetap 1.0
    }
    // ------------------------------

    $namaFile = $kunjungan->file_surat;
    if ($request->hasFile('file_surat')) {
        $file = $request->file('file_surat');
        $namaFile = 'surat_' . str_replace('-', '_', $kunjungan->nomor_kunjungan) . '_' . time() . '.pdf';
        $file->storeAs('surat', $namaFile, 'public');
    }

    // Update database (kolom skor_pelayanan akan terisi karena Model menggunakan $guarded)
    $kunjungan->update([
        'status_layanan' => 'Selesai',
        'user_id' => Auth::id(),
        'waktu_selesai_layanan' => $waktuSelesai,
        'status_sla' => $statusSla,
        'file_surat' => $namaFile,
        'skor_pelayanan' => $skorPelayanan
    ]);

    return back()->with('success', 'Layanan Selesai. Skor Pelayanan: ' . $skorPelayanan);
}

    public function kirimEmailPimpinan(Request $request)
    {
        $request->validate([
            'kunjungan_id' => 'required|exists:kunjungan,id',
            'email_pimpinan' => 'required|email'
        ]);

        $kunjungan = Kunjungan::with(['pengunjung', 'prodi'])->findOrFail($request->kunjungan_id);

        try {
            Mail::to($request->email_pimpinan)->send(new NotifikasiPimpinanMail($kunjungan));
            return back()->with('success', 'Email berhasil diteruskan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengirim email.');
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
            'data_users' => User::all(),
            'data_keperluan' => DB::table('master_keperluan')->get()
        ]);
    }

    public function destroyKeperluan($id)
    {
        if (Auth::user()->role_id != 1) return abort(403);
        DB::table('master_keperluan')->where('id', $id)->delete();
        return back()->with('success', 'Pilihan keperluan berhasil dihapus.');
    }

    public function storeKeperluan(Request $request)
    {
        $request->validate(['keterangan' => 'required|string|max:255']);
        DB::table('master_keperluan')->insert([
            'keterangan' => $request->keterangan,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        return back()->with('success', 'Keperluan baru berhasil ditambahkan.');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return back()->with('success', 'User baru berhasil didaftarkan.');
    }

    public function tanggapanPimpinan(Request $request, $id)
    {
        $request->validate([
            'status_pimpinan' => 'required|in:Disetujui,Ditolak',
            'catatan_pimpinan' => 'nullable|string'
        ]);

        $kunjungan = Kunjungan::where('id', $id)->orWhere('nomor_kunjungan', $id)->firstOrFail();
        $kunjungan->status_pimpinan = $request->status_pimpinan;
        $kunjungan->catatan_pimpinan = $request->catatan_pimpinan;
        $kunjungan->save();

        return back()->with('success', 'Tanggapan berhasil disimpan!');
    }
}
