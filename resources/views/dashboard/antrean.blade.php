@extends('layouts.app')
<style>
    .swal2-backdrop-show {
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
}
</style>

@section('title', 'Manajemen Antrean')

@section('content')
    {{-- SISTEM NOTIFIKASI TOAST POP-UP --}}
    <div id="toast-container" class="fixed bottom-4 md:bottom-10 left-4 right-4 md:left-auto md:right-10 z-[999] flex flex-col gap-4">
        @if(session('success'))
            <div class="toast-item bg-emerald-500 text-white px-6 md:px-8 py-4 md:py-5 rounded-2xl md:rounded-[2rem] shadow-xl flex items-center gap-4 animate-toast-in">
                <div class="flex-shrink-0 w-8 h-8 md:w-10 md:h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-sm md:text-lg"></i>
                </div>
                <div>
                    <p class="text-[9px] md:text-[10px] font-black uppercase tracking-widest opacity-70">Berhasil</p>
                    <p class="font-bold text-xs md:text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-item bg-rose-500 text-white px-8 py-5 rounded-[2rem] shadow-[0_20px_50px_rgba(244,63,94,0.3)] flex items-center gap-4 animate-toast-in">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Gagal</p>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- HEADER SECTION & FILTERS --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-6 md:mb-8 gap-4">
        <div>
            <h2 class="text-2xl md:text-4xl font-black text-gray-800 dark:text-white tracking-tight leading-none">Manajemen Antrean</h2>
            <p class="text-slate-400 dark:text-slate-400 text-xs md:text-sm font-medium mt-2 md:mt-3">Monitor dan kelola riwayat antrean secara mendetail.</p>
        </div>

{{-- FORM PENCARIAN DAN FILTER --}}
<form action="{{ url()->current() }}" method="GET" onsubmit="handleCariLoading(event, this)" class="w-full lg:w-auto flex flex-col sm:flex-row gap-3 items-center">
    @php
        $isSuper = $user->role_id == 1 || $user->role_id == 3;
    @endphp

    {{-- Filter Prodi dengan Validasi Role Admin/Petugas --}}
    <div class="w-full sm:w-64 relative">
        <select name="prodi_id"
            onchange="handleSelectProdiLoading(this)"
            {{ !$isSuper ? 'disabled' : '' }}
            class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl pl-4 pr-10 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-50 dark:bg-slate-900 cursor-not-allowed text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-800' : '' }}">

            @if($isSuper)
                <option value="" class="dark:bg-slate-800"> Seluruh Program Studi</option>
                @foreach($daftar_prodi ?? [] as $p)
                    <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                         🎓 {{ $p->nama }}
                    </option>
                @endforeach
            @else
                <option selected class="dark:bg-slate-800">
                     🎓 {{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}
                </option>
            @endif
        </select>

        <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500 text-xs">
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </div>

    {{-- Input Pencarian Nama / Nomor Kunjungan --}}
    <div class="w-full sm:w-64 relative">
        <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500"></i>
        <input type="text"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama / no. kunjungan..."
            class="w-full pl-12 pr-10 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-medium focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none shadow-sm transition-all text-slate-700 dark:text-slate-200">

        @if(request('search') || request('prodi_id'))
            <a href="{{ url()->current() }}"
            onclick="handleResetLoading()"
            class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors"
            title="Clear Filter">
                <i class="fa-solid fa-circle-xmark"></i>
            </a>
        @endif
    </div>

    {{-- Tombol Submit Cari --}}
    <button type="submit" id="btnSubmitCari"
    class="w-full sm:w-auto bg-gradient-to-r from-slate-900 via-blue-900 to-red-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all disabled:opacity-50 disabled:scale-100 disabled:cursor-not-allowed shadow-blue-900/30">
    <i class="fa-solid fa-magnifying-glass mr-2"></i>
    <span>Cari</span>
</button>
</form>
    </div>

    {{-- DASHBOARD CARD STATS SUMMARY --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
    {{-- CARD: ANTRE --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 flex items-center justify-center text-xl shadow-inner">
            <i class="fa-solid fa-clock-rotate-left"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Antre</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Antre')) }}</h4>
        </div>
    </div>

    {{-- CARD: DIPROSES --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 dark:text-indigo-400 flex items-center justify-center text-xl shadow-inner">
            <i class="fa-solid fa-spinner fa-spin"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Diproses</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Diproses')) }}</h4>
        </div>
    </div>

    {{-- CARD: SELESAI --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 dark:text-emerald-400 flex items-center justify-center text-xl shadow-inner">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Selesai</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Selesai')) }}</h4>
        </div>
    </div>

    {{-- CARD: DITOLAK --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 dark:text-rose-400 flex items-center justify-center text-xl shadow-inner">
            <i class="fa-solid fa-circle-xmark"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Ditolak</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Ditolak')) }}</h4>
        </div>
    </div>

    {{-- CARD: TERLAMBAT --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-500 dark:text-red-400 flex items-center justify-center text-xl shadow-inner">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Terlambat</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_sla', 'TERLAMBAT')) }}</h4>
        </div>
    </div>

    {{-- CARD: TOTAL --}}
    <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4 transition-colors duration-300">
        <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg">
            <i class="fa-solid fa-layer-group"></i>
        </div>
        <div>
            <p class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Total</p>
            <h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan) }}</h4>
        </div>
    </div>
</div>

    {{-- TABLE CONTAINER --}}
    <div class="bg-white dark:bg-slate-800 rounded-2xl md:rounded-[2.5rem] border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden transition-colors duration-300">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-slate-900/40">
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">ID</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">Nama Pengunjung</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest text-center">Status Layanan</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest text-center">Estimasi SLA</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest text-center">Status SLA</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest text-center">Tanggal</th>
                        <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                    @forelse($data_kunjungan as $k)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-900/30 transition-colors group">
                        <td class="px-6 md:px-8 py-4 md:py-6 font-bold text-gray-800 dark:text-slate-200 text-sm md:text-base">#{{ $k->nomor_kunjungan }}</td>
                        <td class="px-6 md:px-8 py-4 md:py-6">
                            <p class="font-extrabold text-gray-800 dark:text-white text-sm md:text-base">{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}</p>

                            {{-- JENIS KEPERLUAN --}}
                            <div class="mb-2 mt-1">
                                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Jenis</p>
                                <p class="text-sm font-bold text-slate-700 dark:text-slate-300 italic leading-relaxed">
                                    {{ $k->keperluan_master->keterangan ?? '-' }}
                                </p>
                            </div>

                            {{-- DETAIL --}}
                            @if(!empty($k->keperluan) && $k->keperluan != '-')
                                <div class="mb-2">
                                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Detail</p>
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-400 italic leading-relaxed">
                                        "{{ Str::limit($k->keperluan, 120) }}"
                                    </p>
                                </div>
                            @endif

                            @if($k->catatan_pimpinan)
                                <div class="mt-3 p-3 rounded-xl shadow-sm {{ $k->status_pimpinan == 'Setuju' ? 'bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50' }}">
                                    <p class="text-[9px] font-black uppercase tracking-widest mb-1 {{ $k->status_pimpinan == 'Setuju' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        <i class="fa-solid fa-comment-medical mr-1"></i> Respon Pimpinan : {{ $k->status_pimpinan }}
                                    </p>
                                    <p class="text-[11px] font-bold italic leading-relaxed {{ $k->status_pimpinan == 'Setuju' ? 'text-emerald-900 dark:text-emerald-300' : 'text-rose-900 dark:text-rose-300' }}">
                                        "{{ $k->catatan_pimpinan }}"
                                    </p>
                                </div>
                            @endif

                            @if($k->status_layanan == 'Selesai')
                                <div class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl">
                                    <p class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-widest">Durasi Pelayanan</p>
                                    <p class="text-sm font-extrabold text-emerald-700 dark:text-emerald-300">{{ $k->durasi_layanan }}</p>
                                </div>
                            @endif
                        </td>
<td class="px-6 md:px-8 py-4 md:py-6 text-center">
                            @php
                                $color = match($k->status_layanan) {
                                    'Selesai' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
                                    'Diproses' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400',
                                    'Ditolak' => 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400',
                                    default => 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                                };
                            @endphp
                            <span class="px-3 md:px-4 py-1 {{ $color }} rounded-full text-[8px] md:text-[9px] font-black uppercase tracking-widest inline-block whitespace-nowrap">
                                {{ $k->status_layanan }}
                            </span>
                        </td>
                        <td class="px-6 md:px-8 py-4 md:py-6 text-center text-sm font-bold text-gray-600 dark:text-slate-400">
                            {{ $k->estimasi_sla ?? '-' }} {{ $k->satuan_sla ?? '' }}
                        </td>
                        <td class="px-8 py-6 text-center">
                            @php
                                $status_sla = strtolower(trim($k->status_sla ?? ''));
                                $status_layanan = strtolower(trim($k->status_layanan ?? ''));
                            @endphp

                            @if($status_layanan == 'selesai')
                                @if($status_sla == 'tepat waktu')
                                    <span class="text-emerald-500 dark:text-emerald-400 font-black text-[10px] flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-circle-check"></i> TEPAT WAKTU
                                    </span>
                                @elseif($status_sla == 'terlambat')
                                    <span class="text-rose-500 dark:text-rose-400 font-black text-[10px] flex items-center justify-center gap-1">
                                        <i class="fa-solid fa-circle-exclamation"></i> TERLAMBAT
                                    </span>
                                @else
                                    <span class="text-gray-400 dark:text-slate-500 text-[10px] italic">Data SLA: "{{ $k->status_sla ?? 'Null/Kosong' }}"</span>
                                @endif
                            @elseif($status_layanan == 'ditolak')
                                <span class="text-rose-600 dark:text-rose-400 font-black text-[10px] flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-ban"></i> GAGAL
                                </span>
                            @else
                                <span class="text-indigo-400 dark:text-indigo-400 text-[9px] font-black uppercase italic tracking-tighter">Sedang Berjalan</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-center">
                            <p class="text-gray-800 dark:text-slate-200 font-bold text-sm">{{ \Carbon\Carbon::parse($k->tanggal)->translatedFormat('d M Y') }}</p>
                            <p class="text-[9px] font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">{{ $k->hari_kunjungan }}</p>
                        </td>
                        <td class="px-6 md:px-8 py-4 md:py-6 text-center">
                            <div class="flex justify-center gap-1.5 md:gap-2 items-center">
                                {{-- TOMBOL LIHAT: Selalu muncul untuk semua kondisi status layanan --}}
                                <a href="{{ url('/status/'.$k->nomor_kunjungan) }}?view=admin" target="_blank" class="flex-shrink-0 w-8 h-8 md:w-9 md:h-9 flex items-center justify-center bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-slate-300 rounded-lg md:rounded-xl hover:bg-slate-800 dark:hover:bg-slate-900 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-eye text-[10px] md:text-xs"></i>
                                </a>

                                @if($user->role_id == 2)
                                    {{-- KONDISI 1: STATUS ANTRE (BELUM MULAI) --}}
                                    {{-- Hanya memunculkan tombol Mulai Proses dan Tolak saja --}}
                                    @if($k->status_layanan == 'Antre')
                                        {{-- TOMBOL MULAI PROSES --}}
                                        <button type="button" onclick="bukaModalProsesSLA('{{ $k->nomor_kunjungan }}')" class="w-9 h-9 flex items-center justify-center bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-600 dark:hover:bg-indigo-500 hover:text-white transition-all shadow-sm" title="Mulai Proses">
                                            <i class="fa-solid fa-play text-xs"></i>
                                        </button>
                                        
                                        {{-- TOMBOL TOLAK ANTREAN --}}
                                        <button type="button" onclick="bukaModalTolak('{{ $k->id }}')" class="w-9 h-9 flex items-center justify-center bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 dark:hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Tolak Antrean">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>

                                    {{-- KONDISI 2: STATUS DIPROSES --}}
                                    {{-- Memunculkan seluruh tombol operasional kelola dokumen --}}
                                    @elseif(strtolower($k->status_layanan) == 'diproses')
                                        {{-- TOMBOL EMAIL --}}
                                        @if(!$k->is_email_sent)
                                            <button type="button" onclick="bukaModalEmail('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}', '{{ addslashes($k->keperluan) }}')" class="w-9 h-9 flex items-center justify-center rounded-xl shadow-sm transition-all bg-blue-50 dark:bg-blue-950/30 text-blue-500 dark:text-blue-400 hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white" title="Kirim Email ke Pimpinan">
                                                <i class="fa-solid fa-envelope text-xs"></i>
                                            </button>
                                        @endif

                                        {{-- TOMBOL TERUSKAN --}}
                                        @if(!$k->is_forwarded)
                                            <button type="button" onclick="bukaModalForward('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')" class="w-9 h-9 flex items-center justify-center rounded-xl shadow-sm transition-all bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 hover:bg-violet-600 dark:hover:bg-violet-500 hover:text-white" title="Teruskan ke Pimpinan">
                                                <i class="fa-solid fa-share-nodes text-xs"></i>
                                            </button>
                                        @endif

                                        @if($k->is_forwarded && !$k->is_email_sent)
                                            <button type="button" onclick="peringatanEmailWajib('{{ $k->id }}', '{{ addslashes($k->pengunjung->nama_lengkap ?? 'Umum') }}', '{{ addslashes($k->keperluan ?? '-') }}')" class="w-9 h-9 flex items-center justify-center bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-500 dark:hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Wajib Email Konfirmasi">
                                                <i class="fa-solid fa-triangle-exclamation text-xs"></i>
                                            </button>
                                        @endif

                                        @if($k->is_email_sent)
                                            <button type="button" disabled class="w-9 h-9 flex items-center justify-center bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-500 rounded-xl cursor-not-allowed shadow-sm" title="Email Sudah Terkirim">
                                                <i class="fa-solid fa-envelope-circle-check text-xs"></i>
                                            </button>
                                        @endif

                                        {{-- TOMBOL SELESAI LAYANAN --}}
                                        <form id="formSelesaiLayanan-{{ $k->id }}" action="{{ route('kunjungan.selesai', $k->id) }}" method="POST" class="m-0">
                                            @csrf
                                            <button type="button" onclick="konfirmasiSelesai('{{ $k->id }}')" class="w-9 h-9 flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-xl hover:bg-emerald-600 dark:hover:bg-emerald-500 hover:text-white transition-all shadow-sm" title="Selesai">
                                                <i class="fa-solid fa-check text-xs"></i>
                                            </button>
                                        </form>

                                        {{-- TOMBOL UPLOAD FILE --}}
                                        @if(empty($k->file_surat))
                                            <button type="button" onclick="bukaModalUpload('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')" class="w-9 h-9 flex items-center justify-center bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-500 dark:hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Upload File">
                                                <i class="fa-solid fa-paperclip text-xs"></i>
                                            </button>
                                        @endif
                                    @endif
                                    
                                    {{-- KONDISI 3: STATUS DITOLAK / SELESAI --}}
                                    {{-- Sengaja dikosongkan agar jika statusnya "Ditolak", maka baris tombol operasional tidak akan dirender, menyisakan tombol "Lihat" saja di paling atas --}}
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-8 py-20 text-center text-gray-400 dark:text-slate-500 bg-white dark:bg-slate-800">Data tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

{{-- MODAL ESTIMASI SLA --}}
    <div id="modalProsesSLA" class="fixed inset-0 z-[100] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-[1.5rem] md:rounded-[2.5rem] p-6 md:p-10 max-w-md w-full shadow-2xl animate-modal-up relative transition-colors duration-300">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white tracking-tight">Estimasi Waktu</h3>
                <button type="button" id="btnCloseSLA" onclick="tutupModalSLA()" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-300 hover:bg-rose-50 dark:hover:bg-rose-900/50 hover:text-rose-500 dark:hover:text-rose-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formSLA" method="POST" onsubmit="handleModalLoading(event, 'formSLA', 'btnSubmitSLA', 'btnCloseSLA')">
                @csrf
                <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/50">
                    <p class="text-[10px] uppercase font-black tracking-widest text-amber-600 dark:text-amber-400 mb-2">Perhatian</p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 font-semibold leading-relaxed">
                        Estimasi hanya bisa diinput <b>1 kali</b>. Pastikan sudah sesuai dengan <b>jenis keperluan</b> dan perkiraan waktu pengerjaan layanan.
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-5 mb-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2">Angka</label>
                        <input type="number" name="estimasi_sla" required class="bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2">Satuan</label>
                        <select name="satuan_sla" class="bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
                            <option value="Menit" class="dark:bg-slate-800">Menit</option>
                            <option value="Hari" class="dark:bg-slate-800">Hari</option>
                        </select>
                    </div>
                </div>
                <button type="submit" id="btnSubmitSLA" class="w-full bg-indigo-600 dark:bg-indigo-500 text-white py-5 rounded-[1.5rem] font-black uppercase tracking-widest shadow-xl hover:bg-indigo-700 dark:hover:bg-indigo-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    Konfirmasi & Mulai
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL EMAIL PIMPINAN --}}
    <div id="modalEmailPimpinan" class="fixed inset-0 z-[100] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start bg-gray-50/50 dark:bg-gray-800/50 gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white">Kirim Email ke Pimpinan</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        <span class="text-amber-500 font-semibold dark:text-amber-400">Penting:</span> Admin wajib mengirimkan email verifikasi ini untuk mengonfirmasi kepada Pimpinan bahwa ada data antrean baru yang memerlukan persetujuan atau tindak lanjut.
                    </p>
                </div>
                <button type="button" id="btnCloseXEmail" onclick="tutupModalEmail()" class="text-gray-400 dark:text-gray-300 hover:text-rose-500 dark:hover:text-rose-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed mt-1 shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formEmail" action="{{ route('kunjungan.kirim-email') }}" method="POST" class="p-6" onsubmit="handleModalLoading(event, 'formEmail', 'btnSubmitEmail', 'btnBatalEmail', 'btnCloseXEmail')">
                @csrf
                <input type="hidden" name="kunjungan_id" id="modal_kunjungan_id">
                
                <div class="mb-5 bg-indigo-50/50 dark:bg-indigo-950/30 p-4 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/50">
                    <p class="text-[10px] font-black text-indigo-500 dark:text-indigo-400 uppercase tracking-widest mb-1">Informasi Kunjungan</p>
                    <p class="font-bold text-gray-800 dark:text-gray-200 text-sm" id="modal_nama_pengunjung"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 italic" id="modal_keperluan_pengunjung"></p>
                </div>

                <div class="mb-6">
                    <label class="block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Email Pimpinan</label>
                    <div class="relative">
                        <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 dark:text-gray-500"></i>
                        <input type="email" name="email_pimpinan" id="email_pimpinan" required placeholder="pimpinan@poliban.ac.id" class="w-full pl-10 pr-4 py-3 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-white focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="btnBatalEmail" onclick="tutupModalEmail()" class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                    <button type="submit" id="btnSubmitEmail" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 rounded-xl shadow-lg flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL FORWARD PIMPINAN --}}
    <div id="modalForwardPimpinan" class="fixed inset-0 z-[120] hidden bg-black/40 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-black text-gray-800 dark:text-white">Teruskan ke Pimpinan</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">Pilih tujuan disposisi layanan</p>
                </div>
                <button type="button" id="btnCloseXForward" onclick="tutupModalForward()" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-gray-400 dark:text-gray-300 hover:text-rose-500 dark:hover:text-rose-400 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form id="formForwardPimpinan" action="{{ route('kunjungan.kirim-massal') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="ids[]" id="forward_kunjungan_id">
                <input type="hidden" name="nama_pengunjung" id="forward_nama_hidden">
                <input type="hidden" name="keperluan_pengunjung" id="forward_keperluan_hidden">

                <div class="mb-6">
                    <div class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-4">
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 dark:text-indigo-400 mb-1">Pengunjung</p>
                        <p id="forward_nama_pengunjung" class="font-bold text-gray-800 dark:text-gray-200 text-sm"></p>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-indigo-500 dark:hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 transition-all cursor-pointer">
                        <input type="radio" name="tujuan_pimpinan" value="kajur" required class="w-5 h-5 text-indigo-600 dark:text-indigo-400">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <p class="font-black text-gray-800 dark:text-gray-200 text-sm">Ketua Jurusan</p>
                                <p class="text-xs text-gray-400 dark:text-gray-400">Kirim ke Kajur Elektro</p>
                            </div>
                        </div>
                    </label>

                    <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-violet-500 dark:hover:border-violet-400 hover:bg-violet-50 dark:hover:bg-violet-950/20 transition-all cursor-pointer">
                        <input type="radio" name="tujuan_pimpinan" value="kaprodi" required class="w-5 h-5 text-violet-600 dark:text-violet-400">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-violet-100 dark:bg-violet-900/50 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <p class="font-black text-gray-800 dark:text-gray-200 text-sm">Ketua Program Studi</p>
                                <p class="text-xs text-gray-400 dark:text-gray-400">Kirim ke Kaprodi terkait</p>
                            </div>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="btnBatalForward" onclick="tutupModalForward()" class="flex-1 py-3 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-all disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                    <button type="button" id="btnSubmitForward" onclick="konfirmasiForward()" class="flex-1 py-3 rounded-2xl bg-violet-600 dark:bg-violet-500 hover:bg-violet-700 dark:hover:bg-violet-600 text-white font-black text-sm shadow-lg dark:shadow-none transition-all disabled:opacity-50 disabled:cursor-not-allowed">Teruskan</button>
                </div>
            </form>
        </div>
    </div>

{{-- MODAL UPLOAD FILE --}}
    <div id="modalUploadFile" class="fixed inset-0 z-[100] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-amber-50/50 dark:bg-amber-950/20">
                <h3 class="text-lg font-black text-amber-800 dark:text-amber-400">Upload File Layanan</h3>
                <button type="button" id="btnCloseXUpload" onclick="tutupModalUpload()" class="text-gray-400 dark:text-gray-300 hover:text-rose-500 dark:hover:text-rose-400 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="formUploadSelesai" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="handleModalLoading(event, 'formUploadSelesai', 'btnSubmitUpload', 'btnBatalUpload', 'btnCloseXUpload')">
                @csrf
                <div class="mb-5 bg-amber-50 dark:bg-amber-950/30 p-4 rounded-2xl border border-amber-100 dark:border-amber-900/50">
                    <p class="font-bold text-gray-800 dark:text-gray-200 text-sm" id="upload_nama_pengunjung"></p>
                    <p class="text-xs text-amber-700 dark:text-amber-300 mt-2 leading-relaxed">
                        {{-- REVISI: Mengubah keterangan deskripsi format berkas dan batas kapasitas --}}
                        Upload dokumen pendukung layanan dalam format <span class="font-bold">PDF, Word (DOC/DOCX), atau Gambar (PNG/JPG/JPEG)</span> dengan ukuran maksimal <span class="font-bold text-amber-600 dark:text-amber-400">10 MB</span>. Status layanan tetap diproses sampai admin menekan tombol selesai.
                    </p>
                </div>

                <div class="mb-6">
                    {{-- REVISI: Mengubah atribut accept agar meloloskan pdf, doc, docx, png, jpg, jpeg --}}
                    <input type="file" name="file_surat" id="inputFileSurat" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-amber-100 dark:file:bg-amber-900/50 file:text-amber-700 dark:file:text-amber-400">
                    {{-- Wadah pesan error jika file melebihi kapasitas 10 MB sebelum disubmit --}}
                    <p id="errorSizeFile" class="hidden mt-2 text-xs text-rose-500 font-bold"></p>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" id="btnBatalUpload" onclick="tutupModalUpload()" class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                    <button type="submit" id="btnSubmitUpload" class="hidden px-5 py-2.5 text-sm font-bold text-white bg-amber-600 dark:bg-amber-500 hover:bg-amber-700 dark:hover:bg-amber-600 rounded-xl shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">Upload File</button>
                </div>
            </form>
        </div>
    </div>

{{-- LOGIKA BARU: MODAL TOLAK ANTREAN (DARI INDEX.BLADE) --}}
<div id="modalTolak" class="fixed inset-0 z-[999] hidden items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[2rem] p-6 shadow-2xl border dark:border-slate-800">
        <div class="mb-5">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Tolak Antrean</h2>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Wajib isi alasan penolakan</p>
        </div>
        
        {{-- REVISI: Menambahkan attribute onsubmit untuk memicu fungsi loading --}}
        <form id="formTolak" method="POST" action="" onsubmit="handleTolakLoading(event)">
            @csrf
            <textarea name="alasan_tolak" required
                class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-rose-100 dark:focus:ring-rose-950 outline-none"
                placeholder="Contoh: Dokumen tidak lengkap / data tidak valid"></textarea>
            
            <div class="flex gap-3 mt-5">
                <button type="button" id="btnBatalTolak" onclick="tutupModalTolak()" class="flex-1 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-black text-xs uppercase disabled:opacity-50 disabled:cursor-not-allowed">Batal</button>
                <button type="submit" id="btnSubmitTolak" class="flex-1 py-3 rounded-2xl bg-rose-600 text-white font-black text-xs uppercase shadow-lg dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">Kirim Penolakan</button>
            </div>
        </form>
    </div>
</div>

    <style>
    .overflow-x-auto::-webkit-scrollbar {
        height: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background-color: #e2e8f0;
        border-radius: 10px;
    }
    .dark .overflow-x-auto::-webkit-scrollbar-thumb {
        background-color: #334155;
    }

    @keyframes toast-in {
        from { transform: translateY(100px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-toast-in { animation: toast-in 0.5s ease forwards; }

    @keyframes modal-up {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    .animate-modal-up { animation: modal-up 0.3s ease-out forwards; }
    </style>

<script>
    let isModalOpen = false; // Flag kontrol sinkronisasi refresh data

    // ==========================================
    // UTILITY: POP-UP LOADING GLOBAL & LOCK
    // ==========================================
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
            backdrop: `
                rgba(15, 23, 42, 0.3)
                backdrop-filter: blur(8px);
                -webkit-backdrop-filter: blur(8px);
            `,
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

    function handleModalLoading(event, formId, ...buttonIds) {
        event.preventDefault();
        const form = document.getElementById(formId);

        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        buttonIds.forEach(id => {
            const btn = document.getElementById(id);
            if (btn) btn.disabled = true;
        });

        const mainBtn = document.getElementById(buttonIds[0]);
        if (mainBtn) {
            mainBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...`;
        }

        showGlobalLoading();
        form.submit();
    }

    // ==========================================
    // MODAL PROSES SLA
    // ==========================================
    function bukaModalProsesSLA(nomorKunjungan) {
        isModalOpen = true;
        const form = document.getElementById('formSLA');
        form.action = "{{ url('/dashboard/mulai-proses') }}/" + nomorKunjungan;
        document.getElementById('modalProsesSLA').classList.remove('hidden');
    }

    function tutupModalSLA() {
        document.getElementById('modalProsesSLA').classList.add('hidden');
        isModalOpen = false;
    }

// ==========================================
    // LOGIKA BARU: JAVASCRIPT MODAL TOLAK
    // ==========================================
    function bukaModalTolak(id) {
        isModalOpen = true;
        const modal = document.getElementById('modalTolak');
        const form = document.getElementById('formTolak');
        
        // Pastikan element tombol di-reset saat modal dibuka kembali
        const btnSubmit = document.getElementById('btnSubmitTolak');
        const btnBatal = document.getElementById('btnBatalTolak');
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = 'Kirim Penolakan';
        }
        if (btnBatal) {
            btnBatal.disabled = false;
        }

        form.action = `/dashboard/tolak/${id}`;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolak');
        const form = document.getElementById('formTolak');
        
        // Reset isi text area saat modal ditutup
        if (form) form.reset();

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        isModalOpen = false;
    }

    // REVISI TAMBAHAN: FUNGSI MEMICU POP-UP LOADING SAAT SUBMIT TOLAK
    function handleTolakLoading(event) {
        const btnSubmit = document.getElementById('btnSubmitTolak');
        const btnBatal = document.getElementById('btnBatalTolak');
        
        // 1. Ubah visual tombol di dalam modal menjadi loading & disabled (anti double-click)
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin mr-2"></i> Mengirim...';
        }
        if (btnBatal) {
            btnBatal.disabled = true;
        }

        // 2. Pemicu utama: Memanggil fungsi pop-up screen loading global bawaan sistem Anda
        if (typeof showGlobalLoading === 'function') {
            showGlobalLoading("Memproses penolakan antrean dan memperbarui status...");
        }
    }

    // ==========================================
    // MODAL EMAIL PIMPINAN
    // ==========================================
    function bukaModalEmail(id, nama, keperluan) {
        isModalOpen = true;
        document.getElementById('modal_kunjungan_id').value = id;
        document.getElementById('modal_nama_pengunjung').innerText = nama;
        document.getElementById('modal_keperluan_pengunjung').innerText = keperluan ? `"${keperluan}"` : '-';
        document.getElementById('modalEmailPimpinan').classList.remove('hidden');
    }

    function tutupModalEmail() {
        document.getElementById('modalEmailPimpinan').classList.add('hidden');
        isModalOpen = false;
    }

// ==========================================
    // MODAL UPLOAD FILE
    // ==========================================
    function bukaModalUpload(id, nama) {
        isModalOpen = true;
        document.getElementById('upload_nama_pengunjung').innerText = nama;
        document.getElementById('formUploadSelesai').action = `/dashboard/upload-file/${id}`;
        document.getElementById('modalUploadFile').classList.remove('hidden');
    }

    document.addEventListener("DOMContentLoaded", function () {
        const inputFile = document.getElementById('inputFileSurat');
        const btnSubmitUpload = document.getElementById('btnSubmitUpload');
        const errorSizeFile = document.getElementById('errorSizeFile');

        if (inputFile && btnSubmitUpload) {
            inputFile.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    const fileSize = this.files[0].size; // Ukuran dalam Bytes
                    const maxSize = 10 * 1024 * 1024; // 10 MB dalam Bytes

                    // REVISI: Validasi tambahan client-side jika melebihi 10 MB tombol disembunyikan dan muncul warning
                    if (fileSize > maxSize) {
                        errorSizeFile.innerText = "⚠️ Ukuran berkas melebihi batas maksimal 10 MB!";
                        errorSizeFile.classList.remove('hidden');
                        this.value = ""; // Reset input file jika tidak valid
                        btnSubmitUpload.classList.add('hidden');
                    } else {
                        errorSizeFile.classList.add('hidden');
                        btnSubmitUpload.classList.remove('hidden');
                    }
                } else {
                    btnSubmitUpload.classList.add('hidden');
                    errorSizeFile.classList.add('hidden');
                }
            });
        }

        @if(session('success_upload_remind'))
        const isDarkMode = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Berkas Berhasil Diunggah!',
            text: "{{ session('success_upload_remind') }}",
            icon: 'success',
            showCancelButton: false,
            confirmButtonText: 'Oke',
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#f8fafc' : '#1f2937',
            confirmButtonColor: '#f59e0b', {{-- Menggunakan warna amber/kuning khas tema sistem kamu --}}
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700 p-6',
                title: 'font-black text-xl tracking-tight text-emerald-600 dark:text-emerald-400',
                htmlContainer: 'text-sm text-gray-500 dark:text-gray-400 mt-3 leading-relaxed font-medium',
                confirmButton: 'rounded-xl font-bold text-sm px-6 py-3 w-full sm:w-auto',
            }
        });
        @endif
        
        @if(session('trigger_email_modal'))
            const targetId = "{{ session('email_kunjungan_id') }}";
            const targetNama = "{{ session('email_nama') }}";
            const targetKeperluan = {!! json_encode(session('email_keperluan')) !!};

            if (typeof bukaModalEmail === 'function' && targetId) {
                bukaModalEmail(targetId, targetNama, targetKeperluan);
            }
        @endif
    });

    function tutupModalUpload() {
        const form = document.getElementById('formUploadSelesai');
        const btnSubmitUpload = document.getElementById('btnSubmitUpload');
        const errorSizeFile = document.getElementById('errorSizeFile');

        if (form) form.reset();
        if (btnSubmitUpload) btnSubmitUpload.classList.add('hidden');
        if (errorSizeFile) errorSizeFile.classList.add('hidden');

        document.getElementById('modalUploadFile').classList.add('hidden');
        isModalOpen = false;
    }

    // ==========================================
    // MODAL FORWARD PIMPINAN
    // ==========================================
    function bukaModalForward(id, nama, keperluan) {
        isModalOpen = true;
        document.getElementById('forward_kunjungan_id').value = id;
        document.getElementById('forward_nama_pengunjung').innerText = nama;
        
        if(document.getElementById('forward_nama_hidden')) {
            document.getElementById('forward_nama_hidden').value = nama;
        }
        if(document.getElementById('forward_keperluan_hidden')) {
            document.getElementById('forward_keperluan_hidden').value = keperluan || '';
        }
        
        document.getElementById('modalForwardPimpinan').classList.remove('hidden');
    }

    function tutupModalForward() {
        document.getElementById('modalForwardPimpinan').classList.add('hidden');
        isModalOpen = false;
    }

    // ==========================================
    // CLOSE MODAL OUTSIDE CLICK
    // ==========================================
    window.onclick = function(event) {
        if (event.target.id && event.target.id.startsWith('modal')) {
            event.target.classList.add('hidden');
            isModalOpen = false;
        }
    }

    // ==========================================
    // SPECIAL HANDLER: FORWARD PIMPINAN
    // ==========================================
    function konfirmasiForward() {
        const form = document.getElementById('formForwardPimpinan');
        const btnSubmit = document.getElementById('btnSubmitForward');
        const btnBatal = document.getElementById('btnBatalForward');
        const btnCloseX = document.getElementById('btnCloseXForward');

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const isDarkMode = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Teruskan ke Pimpinan?',
            text: 'Data disposisi layanan ini akan segera dikirimkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Teruskan',
            cancelButtonText: 'Batal',
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#1f2937' : '#f8fafc',
            confirmButtonColor: '#7c3aed',
            cancelButtonColor: isDarkMode ? '#475569' : '#94a3b8',
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700',
                title: 'font-black text-xl tracking-tight',
                htmlContainer: 'text-sm text-gray-500 dark:text-gray-400',
                confirmButton: 'rounded-xl font-bold text-sm px-5 py-2.5',
                cancelButton: 'rounded-xl font-bold text-sm px-5 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (btnSubmit) btnSubmit.disabled = true;
                if (btnBatal) btnBatal.disabled = true;
                if (btnCloseX) btnCloseX.disabled = true;

                if (btnSubmit) {
                    btnSubmit.innerHTML = `<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memproses...`;
                    btnSubmit.classList.remove('bg-violet-600', 'hover:bg-violet-700');
                    btnSubmit.classList.add('bg-violet-400', 'dark:bg-violet-600/50');
                }

                showGlobalLoading("Sedang merujuk data kunjungan ke pimpinan...");
                form.submit();
            }
        });
    }
</script>

<script>
    // Auto-reload halaman setiap 3 menit jika tidak ada modal yang terbuka
    setInterval(() => {
        const isSwalOpen = Swal.isVisible();
        if (!isModalOpen && !isSwalOpen) {
            window.location.reload();
        }
    }, 180000);

    // ==========================================
    // SPECIAL HANDLER: KONFIRMASI SELESAI LAYANAN
    // ==========================================
    function konfirmasiSelesai(id) {
        const form = document.getElementById(`formSelesaiLayanan-${id}`);
        if (!form) return;

        const isDarkMode = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Selesaikan Layanan?',
            text: 'Pastikan seluruh proses pelayanan kunjungan ini telah selesai dikerjakan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesai',
            cancelButtonText: 'Batal',
            background: isDarkMode ? '#1e293b' : '#ffffff',
            color: isDarkMode ? '#f8fafc' : '#1f2937',
            confirmButtonColor: '#10b981',
            cancelButtonColor: isDarkMode ? '#475569' : '#94a3b8',
            customClass: {
                popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700',
                title: 'font-black text-xl tracking-tight',
                htmlContainer: 'text-sm text-gray-500 dark:text-gray-400',
                confirmButton: 'rounded-xl font-bold text-sm px-5 py-2.5',
                cancelButton: 'rounded-xl font-bold text-sm px-5 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showGlobalLoading("Sedang memperbarui status layanan menjadi selesai...");
                form.submit();
            }
        });
    }

function handleCariLoading(event, formElement) {
    showGlobalLoading("Sedang mencari dan menyinkronkan data, mohon tunggu...");
    const btnCari = document.getElementById('btnSubmitCari');
    if (btnCari) {
        btnCari.disabled = true;
        const icon = btnCari.querySelector('i');
        const text = btnCari.querySelector('span');
        if (icon) icon.className = "fa-solid fa-spinner fa-spin mr-2";
        if (text) text.innerText = "Memuat...";
    }
    return true;
}

function handleSelectProdiLoading(selectElement) {
    showGlobalLoading("Memfilter data program studi...");
    selectElement.form.submit();
}

function handleResetLoading() {
    showGlobalLoading("Membersihkan filter dan memuat ulang data...");
}

function peringatanEmailWajib(id, nama, keperluan) {
    const isDarkMode = document.documentElement.classList.contains('dark');

    Swal.fire({
        title: 'Email Belum Terkirim!',
        text: `Data kunjungan atas nama "${nama}" sudah diteruskan ke pimpinan, namun email konfirmasi belum dikirim. Harap buka form email sekarang.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Buka Form Email',
        cancelButtonText: 'Nanti Saja',
        background: isDarkMode ? '#1e293b' : '#ffffff',
        color: isDarkMode ? '#f8fafc' : '#1f2937',
        confirmButtonColor: '#f59e0b', 
        cancelButtonColor: isDarkMode ? '#475569' : '#94a3b8',
        customClass: {
            popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700 p-6',
            title: 'font-black text-xl tracking-tight text-amber-600 dark:text-amber-400',
            htmlContainer: 'text-sm text-gray-500 dark:text-gray-400 mt-2',
            confirmButton: 'rounded-xl font-bold text-sm px-5 py-2.5',
            cancelButton: 'rounded-xl font-bold text-sm px-5 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            bukaModalEmail(id, nama, keperluan);
        }
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@endsection