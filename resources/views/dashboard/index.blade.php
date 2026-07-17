@extends('layouts.app')

<style>
    .swal2-backdrop-show {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
    }
</style>

@section('title', $judul_dashboard ?? 'Dashboard Admin')

@section('content')
<div class="min-h-screen bg-[#f6f7fb] dark:bg-slate-900 px-4 sm:px-6 lg:px-8 py-6 transition-colors duration-300">

    {{-- LOGIKA DI SISI BLADE UNTUK MENGHITUNG TOKEN AKTIF SECARA AMAN --}}
    @php
        $token_terpakai = 0;
        foreach($data_kunjungan as $t) {
            if (strtoupper(trim($t->status_layanan ?? '')) != 'DITOLAK') {
                // FIX LOOKUP 1: Mengubah pencarian mencocokkan ID kunjungan langsung pada koleksi ulasan data dari controller
                $sudahSurvei = ($data_ulasan ?? collect())->contains('id', $t->id);
                
                // Kunci slot token jika belum selesai pelayanan ATAU belum mengisi ulasan
                if (strtoupper(trim($t->status_layanan ?? '')) != 'SELESAI' || !$sudahSurvei) {
                    $token_terpakai++;
                }
            }
        }
        $isPenuh = $token_terpakai >= 10;
    @endphp

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
        <div class="space-y-2">
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                    Dashboard Admin
                </h1>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 rounded-full text-xs font-black shadow-sm inline-block">
                    Token Aktif: {{ $token_terpakai }}/10
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <span class="px-4 py-1.5 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] sm:text-[11px] font-black flex items-center gap-2 shadow-sm">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    @if($user->role_id==1 || $user->role_id==3)
                        Monitoring Active
                    @else
                        Petugas Aktif
                    @endif
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">
            @php $isSuper = $user->role_id==1 || $user->role_id==3; @endphp

            <div class="relative w-full sm:w-auto">
                <select onchange="filterProdi(this.value)"
                    {{ !$isSuper ? 'disabled' : '' }}
                    class="w-full sm:w-[250px] md:w-[280px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-100 dark:bg-slate-800/50 cursor-not-allowed text-slate-500 dark:text-slate-500' : '' }}">

                    @if($isSuper)
                        <option value="" class="dark:bg-slate-800"> Seluruh Program Studi</option>
                        @foreach($daftar_prodi ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('prodi_id')==$p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    @else
                        <option selected class="dark:bg-slate-800">
                            {{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}
                        </option>
                    @endif
                </select>

                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            {{-- TOMBOL EKSPOR --}}
            <button type="button" onclick="openExportModal()"
                class="bg-gradient-to-r from-[#0b3a82] via-[#1e293b] to-red-600 text-white px-6 py-3.5 sm:py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all text-center">
                <i class="fa-solid fa-file-export mr-2"></i> Laporan Pengunjung
            </button>
        </div>
    </div>

    {{-- BANNER EMERGENCY PERINGATAN DARURAT TOKEN PENUH --}}
    @if($isPenuh)
        <div class="bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 p-5 rounded-2xl mb-8 flex items-start sm:items-center gap-4 shadow-sm animate-pulse">
            <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/50 text-rose-600 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <h4 class="font-black text-rose-700 dark:text-rose-400 text-sm md:text-base uppercase tracking-wide">Peringatan: Kuota Token Penuh (10/10)</h4>
                <p class="text-xs text-rose-600 dark:text-rose-500 mt-1 font-medium">Sistem pendaftaran luar otomatis terkunci. Segera ingatkan mahasiswa berstatus <span class="font-bold text-rose-700 dark:text-rose-300">Selesai (Menunggu Ulasan)</span> melalui WhatsApp di bawah untuk mengisi ulasan ulasan agar slot token dibebaskan.</p>
            </div>
        </div>
    @endif

    {{-- CARD STATISTIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        {{-- TOTAL KUNJUNGAN + TARGET TRACKER --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                        Kuantitas Layanan
                    </p>
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            {{ $total_dilayani ?? 0 }}
                        </h2>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                            / {{ $target_tamu ?? 10 }} Selesai
                        </span>
                    </div>
                </div>
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
            
            <div class="mt-2">
                <div class="flex justify-between items-center text-[11px] font-bold mb-1.5">
                    <span class="text-slate-400 dark:text-slate-500">Progres Target Harian</span>
                    <span class="text-blue-600 dark:text-blue-400">{{ $skor_kuantitas ?? 0 }}%</span>
                </div>
                <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-500 dark:bg-blue-600 rounded-full transition-all duration-500" style="width: {{ $skor_kuantitas ?? 0 }}%"></div>
                </div>
            </div>
        </div>

        {{-- SLA (EFEKTIVITAS) --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                        Efektivitas (SLA)
                    </p>
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                        {{ $efektivitas_persen ?? 0 }}%
                    </h2>
                </div>
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-purple-100 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-clock"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-t border-slate-50 dark:border-slate-700/30 flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
                <span>Mengukur ketepatan waktu durasi pelayanan</span>
            </div>
        </div>

        {{-- SURVEI (KUALITAS) --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group sm:col-span-2 lg:col-span-1">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                        Kualitas (Survei)
                    </p>
                    <div class="flex items-center gap-2">
                        <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                            {{ $kualitas_rating ?? 0 }}
                        </h2>
                        <div class="flex text-amber-400 text-xs gap-0.5 mb-1">
                            @if(is_numeric($kualitas_rating ?? null) && $kualitas_rating > 0)
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="{{ $i <= round($kualitas_rating) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                                @endfor
                            @else
                                <span class="text-slate-400 dark:text-slate-600 font-bold text-xs">Belum ada ulasan</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-100 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                    <i class="fa-solid fa-star"></i>
                </div>
            </div>
            <div class="mt-auto pt-2 border-t border-slate-50 dark:border-slate-700/30 flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
                <i class="fa-solid fa-heart text-rose-500 text-[10px]"></i>
                <span>Berdasarkan akumulasi indeks kepuasan tamu</span>
            </div>
        </div>
    </div>

    {{-- TITLE SECTION ANTREAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 w-full">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Antrean Layanan
            </h3>
        </div>

        @if($data_kunjungan->count() > 6)
            <a href="{{ route('dashboard.antrean') }}" class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-black text-xs uppercase tracking-widest rounded-2xl shadow-sm transition-all flex items-center justify-center gap-2 shrink-0">
                <span>Lihat semua antrean</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        @endif
    </div>

@php
    // FILTER DATA KUNJUNGAN SECARA TEPAT
    $data_internal = $data_kunjungan->filter(fn($k) => strtolower(trim($k->tipe_tamu ?? $k->pengunjung?->tipe_tamu ?? '')) == 'internal');
    $data_eksternal = $data_kunjungan->filter(fn($k) => strtolower(trim($k->tipe_tamu ?? $k->pengunjung?->tipe_tamu ?? '')) == 'eksternal');

    // SOLUSI UTAMA ERROR: Memetakan ulasan menggunakan lookup manual berbasis koleksi data kunjungan aktif
    $ulasan_internal = ($data_ulasan ?? collect())->filter(function($u) use ($data_kunjungan) {
        $kj = $data_kunjungan->first(function($k) use ($u) {
            return (isset($k->id) && isset($u->id) && $k->id == $u->id);
        });
        return $kj && strtolower(trim($kj->tipe_tamu ?? $kj->pengunjung?->tipe_tamu ?? '')) == 'internal';
    });

    $ulasan_eksternal = ($data_ulasan ?? collect())->filter(function($u) use ($data_kunjungan) {
        $kj = $data_kunjungan->first(function($k) use ($u) {
            return (isset($k->id) && isset($u->id) && $k->id == $u->id);
        });
        return $kj && strtolower(trim($kj->tipe_tamu ?? $kj->pengunjung?->tipe_tamu ?? '')) == 'eksternal';
    });
@endphp

{{-- 1. SECTION ANTREAN --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
    @foreach(['Internal' => $data_internal, 'Eksternal' => $data_eksternal] as $label => $data)
    <div>
        <div class="flex items-center gap-3 mb-6">
            <div class="w-1.5 h-8 {{ $label == 'Internal' ? 'bg-indigo-500' : 'bg-amber-500' }} rounded-full"></div>
            <h3 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Tamu {{ $label }} ({{ $data->count() }})</h3>
        </div>
        
        <div class="grid grid-cols-1 gap-4">
            @forelse($data->take(6) as $k)
                @php
                    $status = $k->status_layanan;
                    $config = match($status) {
                        'Antre'    => ['border' => 'border-amber-300', 'badge' => 'bg-amber-100 text-amber-600'],
                        'Diproses' => ['border' => 'border-emerald-300', 'badge' => 'bg-emerald-100 text-emerald-600'],
                        'Selesai'  => ['border' => 'border-blue-300', 'badge' => 'bg-blue-100 text-blue-600'],
                        'Ditolak'  => ['border' => 'border-rose-300', 'badge' => 'bg-rose-100 text-rose-600'],
                        default    => ['border' => 'border-slate-300', 'badge' => 'bg-slate-100 text-slate-600'],
                    };

                    // FIX LOOKUP 2: Menyesuaikan validasi dengan struktur objek kunjungan yang dikirim oleh Controller
                    $hasSurvey = ($data_ulasan ?? collect())->contains('id', $k->id);
                    $isHutangSurvei = ($k->status_layanan == 'Selesai' && !$hasSurvey);

                    // Normalisasi Nomor WhatsApp
                    $noWaRaw = $k->pengunjung->no_telepon ?? $k->no_telepon ?? '';
                    $noWa = preg_replace('/[^0-9]/', '', $noWaRaw);
                    if (str_starts_with($noWa, '0')) {
                        $noWa = '62' . substr($noWa, 1);
                    } elseif (!str_starts_with($noWa, '62') && !empty($noWa)) {
                        $noWa = '62' . $noWa;
                    }

                    $pesanWa = urlencode("Halo *" . ($k->nama_lengkap ?? $k->pengunjung?->nama_lengkap ?? 'Umum') . "* (No. Antrean: *" . $k->nomor_kunjungan . "*),\n\nPelayanan administrasi Anda di Jurusan Teknik Elektro telah Selesai. Mohon luangkan waktu 1 menit untuk mengisi survei ulasan kepuasan pada tautan resmi berikut agar token antrean Anda terbebaskan dan sistem berjalan normal kembali:\n\n" . route('survey.form', $k->nomor_kunjungan) . "\n\nTerika kasih atas bantuannya!");
                @endphp

                <div class="bg-white dark:bg-slate-800 rounded-[2rem] border-2 {{ $config['border'] }} p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group relative overflow-hidden">
                    <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-50 dark:bg-slate-700/30 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
                    
                    <div class="relative z-10">
                        {{-- HEADER CARD --}}
                        <div class="flex justify-between items-start mb-4">
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $config['badge'] }}">{{ $k->status_layanan }}</span>
                            <div class="text-right">
                                <span class="block text-[10px] font-black bg-slate-100 dark:bg-slate-700 px-3 py-1 rounded-xl text-slate-600 dark:text-slate-300">{{ $k->nomor_kunjungan }}</span>
                                <p class="text-[9px] text-slate-400 font-bold mt-1"><i class="fa-regular fa-clock mr-1"></i>{{ $k->created_at->format('H:i') }} WITA</p>
                            </div>
                        </div>

                        {{-- NAMA, INSTANSI & WHATSAPP --}}
                        <h4 class="text-lg font-black text-slate-900 dark:text-white leading-tight">{{ $k->nama_lengkap ?? $k->pengunjung?->nama_lengkap ?? 'Pengunjung' }}</h4>
                        <div class="flex items-center gap-3 mt-1 mb-4 flex-wrap">
                            <p class="text-xs font-bold text-slate-400"><i class="fa-solid fa-building mr-1"></i> {{ $k->pengunjung->asal_instansi ?? '-' }}</p>
                            @if(!empty($noWa))
                                <p class="text-xs font-bold text-slate-400"><i class="fa-solid fa-phone mr-1"></i> +{{ $noWa }}</p>
                            @endif
                        </div>

                        {{-- BOX KEPERLUAN --}}
                        <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-3.5 border border-slate-100 dark:border-slate-700">
                            <p class="text-[8px] uppercase font-black text-slate-400 tracking-widest mb-1">Keperluan</p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 italic mb-2">{{ $k->keperluan_master->keterangan ?? '-' }}</p>
                            @if(!empty($k->keperluan))
                                <p class="text-[8px] uppercase font-black text-slate-400 tracking-widest mb-1 mt-2">Detail</p>
                                <p class="text-[11px] font-medium text-slate-600 dark:text-slate-400 leading-relaxed italic line-clamp-2">"{{ $k->keperluan }}"</p>
                            @endif
                        </div>

                        {{-- EXTRA INFO --}}
                        <div class="mt-4 space-y-2">
                            @if($k->status_layanan == 'Ditolak' && !empty($k->alasan_tolak))
                                <div class="p-2.5 bg-rose-50 dark:bg-rose-950/30 rounded-xl border border-rose-100 dark:border-rose-900/50">
                                    <p class="text-[8px] font-black uppercase text-rose-600 dark:text-rose-400 tracking-widest">Alasan Penolakan</p>
                                    <p class="text-[11px] font-bold text-rose-700 dark:text-rose-300">"{{ $k->alasan_tolak }}"</p>
                                </div>
                            @endif

                            @if(!empty($k->catatan_pimpinan))
                                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/30 rounded-xl border border-indigo-100 dark:border-indigo-900/50">
                                    <p class="text-[8px] font-black uppercase text-indigo-600 dark:text-indigo-400 tracking-widest">Respon Pimpinan</p>
                                    <p class="text-[11px] font-bold text-indigo-700 dark:text-indigo-300">"{{ $k->catatan_pimpinan }}"</p>
                                </div>
                            @endif

                            @php
                                $waktuSelesai = !empty($k->waktu_selesai_layanan) ? $k->waktu_selesai_layanan : $k->updated_at;
                                $isSelesai = ($k->status_layanan == 'Selesai');
                            @endphp

                            @if(in_array($k->status_layanan, ['Selesai', 'Ditolak']) && !empty($waktuSelesai))
                                <div class="text-[10px] {{ $isSelesai ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} font-bold flex items-center justify-between">
                                    <span>
                                        <i class="fa-solid {{ $isSelesai ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i> 
                                        {{ $isSelesai ? 'Selesai' : 'Ditolak' }}: {{ \Carbon\Carbon::parse($waktuSelesai)->format('H:i') }} WITA
                                    </span>
                                    
                                    {{-- TOMBOL WHATSAPP UNTUK MENAGIH SURVEI KEPADA MAHASISWA --}}
                                    @if($isHutangSurvei && !empty($noWa))
                                        <a href="https://wa.me/{{ $noWa }}?text={{ $pesanWa }}" target="_blank"
                                           class="px-3 py-1 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg shadow font-black text-[9px] uppercase tracking-wider animate-pulse flex items-center gap-1">
                                            <i class="fa-brands fa-whatsapp text-xs"></i> Tagih Ulasan
                                        </a>
                                    @endif
                                </div>
                            @endif
                            
                            @if($isHutangSurvei)
                                <div class="p-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 rounded-xl text-[9px] font-black text-amber-600 dark:text-amber-400 uppercase tracking-wider text-center">
                                    ⚠️ Menunggu Ulasan Tamu (Slot Token Terkunci)
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-slate-50 dark:bg-slate-800/50 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem] py-10 text-center text-slate-400">Belum ada antrean</div>
            @endforelse
        </div>
    </div>
    @endforeach
</div>

{{-- 2. SECTION ULASAN --}}
<div class="flex items-center gap-3 mb-6">
    <div class="w-1.5 h-8 bg-indigo-500 rounded-full"></div>
    <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Ulasan Terbaru</h3>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    @foreach(['Internal' => $ulasan_internal, 'Eksternal' => $ulasan_eksternal] as $label => $data)
    <div class="space-y-4">
        <h4 class="text-[10px] font-black uppercase text-indigo-400 tracking-[0.2em] italic mb-2">{{ $label }}</h4>
        @forelse($data->take(3) as $item)
            @php 
                // Kalkulasi rating rata-rata dari detail survey
                $detail = $item->survey->detail ?? null;
                if ($detail) {
                    $p1 = $detail->p1 ?? 0; $p2 = $detail->p2 ?? 0; $p3 = $detail->p3 ?? 0; $p4 = $detail->p4 ?? 0; $p5 = $detail->p5 ?? 0;
                    $avg = ($p1 + $p2 + $p3 + $p4 + $p5) / 5;
                } else {
                    $p1 = $item->p1 ?? 0; $p2 = $item->p2 ?? 0; $p3 = $item->p3 ?? 0; $p4 = $item->p4 ?? 0; $p5 = $item->p5 ?? 0;
                    $avg = ($p1 + $p2 + $p3 + $p4 + $p5) > 0 ? ($p1 + $p2 + $p3 + $p4 + $p5) / 5 : 5;
                }
            @endphp
            {{-- CARD ULASAN PREMIUM --}}
            <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-all">
                <div class="flex gap-1 text-amber-400 mb-4">
                    @for($i=1; $i<=5; $i++) 
                        <i class="fa-solid fa-star text-[10px] {{ $i <= round($avg) ? '' : 'text-slate-100 dark:text-slate-700' }}"></i> 
                    @endfor
                </div>
                <p class="text-slate-700 dark:text-slate-300 font-bold text-sm leading-relaxed italic mb-6">
                    "{!! $item->survey->kritik_saran ?? 'Hanya memberikan rating.' !!}"
                </p>
                <div class="pt-4 border-t border-slate-50 dark:border-slate-700">
                    <span class="text-slate-900 dark:text-white font-black text-xs uppercase tracking-widest">Anonim</span>
                </div>
            </div>
        @empty
            <div class="p-6 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem] text-center text-slate-400 text-xs">Belum ada ulasan</div>
        @endforelse
    </div>
    @endforeach
</div>

{{-- MODAL EKSPOR PERIODE --}}
<div id="exportModal" class="fixed inset-0 z-[999] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-[1.5rem] md:rounded-[2.5rem] p-6 md:p-10 max-w-md w-full shadow-2xl animate-modal-up relative transition-colors duration-300">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white tracking-tight">Periode Laporan</h3>
                <p class="text-slate-400 dark:text-gray-400 text-xs font-medium mt-1">Tentukan tanggal penarikan data laporan</p>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-300 hover:bg-rose-50 dark:hover:bg-rose-900/50 hover:text-rose-500 dark:hover:text-rose-400 transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="mb-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
            <p class="text-[10px] uppercase font-black tracking-widest text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-circle-info mr-1"></i> Informasi Ekspor
            </p>
            <p class="text-xs text-indigo-900 dark:text-indigo-300 font-semibold leading-relaxed">
                Data laporan yang diunduh akan disesuaikan otomatis berdasarkan rentang tanggal dan filter aktif pimpinan/prodi Anda.
            </p>
        </div>

        <div class="space-y-5 mb-8">
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2 tracking-widest">Tanggal Awal</label>
                <input type="date" id="exportStartDate" required
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2 tracking-widest">Tanggal Akhir</label>
                <input type="date" id="exportEndDate" required
                    class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button onclick="downloadLaporan('xlsx')"
                class="flex items-center justify-center gap-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
                <i class="fa-regular fa-file-excel text-base"></i> Excel
            </button>
            <button onclick="downloadLaporan('pdf')"
                class="flex items-center justify-center gap-2.5 bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
                <i class="fa-regular fa-file-pdf text-base"></i> PDF
            </button>
        </div>
    </div>
</div>

{{-- MODAL LOADING DOWNLOAD LAPORAN --}}
<div id="loading-modal" class="fixed inset-0 z-[1000] hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-white dark:bg-gray-800 p-6 md:p-8 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-gray-700 flex flex-col items-center max-w-xs w-full text-center animate-modal-up">
        <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent dark:border-indigo-400 dark:border-t-transparent rounded-full animate-spin mb-4"></div>
        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider mb-1">Menyiapkan Dokumen</h3>
        <p class="text-[11px] text-slate-400 dark:text-gray-400 leading-relaxed">Mohon tunggu, sistem sedang merangkum data laporan...</p>
    </div>
</div>

{{-- MODAL TOLAK ANTREAN --}}
<div id="modalTolak" class="fixed inset-0 z-[999] hidden items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[2rem] p-6 shadow-2xl border dark:border-slate-800">
        <div class="mb-5"><h2 class="text-xl font-black text-slate-900 dark:text-white">Tolak Antrean</h2><p class="text-sm text-slate-400 mt-1">Wajib isi alasan penolakan</p></div>
        <form id="formTolak" method="POST" action="">
            @csrf
            <textarea name="alasan_tolak" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-rose-100 outline-none" placeholder="Contoh: Dokumen tidak lengkap / data tidak valid"></textarea>
            <div class="flex gap-3 mt-5">
                <button type="button" onclick="tutupModalTolak()" class="flex-1 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 font-black text-xs uppercase">Batal</button>
                <button type="submit" onclick="showGlobalLoading('Mengirim penolakan...')" class="flex-1 py-3 rounded-2xl bg-rose-600 text-white font-black text-xs uppercase shadow-lg">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
let isModalOpen = false;

    function openExportModal() {
        document.getElementById('exportStartDate').value = '';
        document.getElementById('exportEndDate').value = '';
        const modal = document.getElementById('exportModal');
        isModalOpen = true;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeExportModal() {
        const modal = document.getElementById('exportModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        isModalOpen = false;
    }

    function downloadLaporan(type) {
        const startDate = document.getElementById('exportStartDate').value;
        const endDate = document.getElementById('exportEndDate').value;

        if (!startDate || !endDate) {
            alert('Silakan tentukan rentang tanggal awal dan akhir terlebih dahulu.');
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const prodiId = urlParams.get('prodi_id') || '';

        const loadingModal = document.getElementById('loading-modal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }

        closeExportModal();
        isModalOpen = true;

        window.location = '/laporan/pengunjung' +
                          '?type=' + type +
                          '&start_date=' + startDate +
                          '&end_date=' + endDate +
                          '&prodi_id=' + prodiId;

        setTimeout(function() {
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            isModalOpen = false;
        }, 15000);
    }

    function filterProdi(val) {
        const url = new URL(window.location.href);
        if (val) {
            url.searchParams.set('prodi_id', val);
        } else {
            url.searchParams.delete('prodi_id');
        }
        window.location.href = url.toString();
    }

    function bukaModalTolak(id) {
        const modal = document.getElementById('modalTolak');
        const form = document.getElementById('formTolak');
        isModalOpen = true;
        form.action = `/dashboard/tolak/${id}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolak');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        isModalOpen = false;
    }

    function showGlobalLoading(pesanText = "Sedang memproses data, mohon tunggu...") {
        const isDarkMode = document.documentElement.classList.contains('dark');
        window.onclick = null;
        Swal.fire({
            title: 'Memproses Data',
            text: pesanText,
            allowOutsideClick: false,
            allowEscapeKey: false,
            allowEnterKey: false,
            showConfirmButton: false,
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#f8fafc' : '#1f2937',
            backdrop: `rgba(15, 23, 42, 0.3) backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);`,
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700 p-8',
                title: 'font-black text-xl tracking-tight',
                htmlContainer: 'text-sm text-gray-500 dark:text-gray-400 mt-2'
            },
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    // Auto Refresh Halaman Berkala jika modal sedang tertutup
    setInterval(() => { 
        if (!isModalOpen && !Swal.isVisible()) {
            window.location.reload(); 
        }
    }, 180000);
</script>
@endsection