@extends('layouts.app')

@section('title', 'Manajemen Antrean')

@section('content')
    {{-- SCRIPT ALPINE.JS UNTUK FILTER TAB --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        .swal2-backdrop-show {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.4) !important;
        }
        .overflow-x-auto::-webkit-scrollbar { height: 4px; }
        .overflow-x-auto::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 10px; }
        .dark .overflow-x-auto::-webkit-scrollbar-thumb { background-color: #334155; }
        @keyframes toast-in { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-toast-in { animation: toast-in 0.5s ease forwards; }
        @keyframes modal-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-modal-up { animation: modal-up 0.3s ease-out forwards; }
    </style>

    {{-- KALKULASI LOGIKA TOKEN SISTEM SECARA REAL-TIME --}}
    @php
        $tokenTerpakai = 0;
        foreach($data_kunjungan as $t) {
            $statusLayananClean = strtoupper(trim($t->status_layanan ?? ''));
            if ($statusLayananClean != 'DITOLAK') {
                // Token dianggap tertahan jika belum Selesai ATAU ulasannya benar-benar kosong
                if ($statusLayananClean != 'SELESAI' || empty($t->survey)) {
                    $tokenTerpakai++;
                }
            }
        }
        $isPenuh = $tokenTerpakai >= 10;
    @endphp

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

    {{-- BANNER PERINGATAN DARURAT JIKA KUOTA TOKEN MAKSIMAL (10/10) --}}
    @if($isPenuh)
        <div class="bg-rose-50 dark:bg-rose-950/40 border-l-4 border-rose-500 p-5 rounded-2xl mb-6 flex items-start sm:items-center gap-4 shadow-sm animate-pulse">
            <div class="w-12 h-12 bg-rose-100 dark:bg-rose-900/50 text-rose-600 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <h4 class="font-black text-rose-700 dark:text-rose-400 text-sm md:text-base uppercase tracking-wide">Peringatan: Token Antrean Penuh (10/10)</h4>
                <p class="text-xs text-rose-600 dark:text-rose-500 mt-1 font-medium">Sistem otomatis berhenti menerima pendaftaran antrean baru. Harap segera hubungi mahasiswa yang berstatus <span class="font-bold text-rose-700 dark:text-rose-300">Selesai (Menunggu Ulasan)</span> lewat WhatsApp agar token dibebaskan.</p>
            </div>
        </div>
    @endif

    {{-- HEADER SECTION & FILTERS --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end mb-6 md:mb-8 gap-4">
        <div>
            <div class="flex items-center gap-3 flex-wrap">
                <h2 class="text-2xl md:text-4xl font-black text-gray-800 dark:text-white tracking-tight leading-none">Manajemen Antrean</h2>
                <span class="px-3 py-1 bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-400 rounded-full text-xs font-black shadow-sm inline-block">
                    Token Aktif: {{ $tokenTerpakai }}/10
                </span>
            </div>
            <p class="text-slate-400 dark:text-slate-400 text-xs md:text-sm font-medium mt-2 md:mt-3">Monitor dan kelola riwayat antrean serta waktu pelayanan SLA secara otomatis.</p>
        </div>

        {{-- FORM PENCARIAN DAN FILTER --}}
        <form action="{{ url()->current() }}" method="GET" onsubmit="handleCariLoading(event, this)" class="w-full lg:w-auto flex flex-col sm:flex-row gap-3 items-center">
            @php $isSuper = $user->role_id == 1 || $user->role_id == 3; @endphp

            <div class="w-full sm:w-64 relative">
                <select name="prodi_id" onchange="handleSelectProdiLoading(this)" {{ !$isSuper ? 'disabled' : '' }}
                    class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl pl-4 pr-10 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 focus:border-indigo-500 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-50 dark:bg-slate-900 cursor-not-allowed text-slate-400 border-slate-200' : '' }}">
                    @if($isSuper)
                        <option value="" class="dark:bg-slate-800">Seluruh Program Studi</option>
                        @foreach($daftar_prodi ?? [] as $p)
                            <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }} class="dark:bg-slate-800">{{ $p->nama }}</option>
                        @endforeach
                    @else
                        <option selected class="dark:bg-slate-800">{{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}</option>
                    @endif
                </select>
                <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs"><i class="fa-solid fa-chevron-down"></i></div>
            </div>

            <div class="w-full sm:w-64 relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / no. kunjungan..."
                    class="w-full pl-12 pr-10 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-medium focus:border-indigo-500 outline-none shadow-sm transition-all text-slate-700 dark:text-slate-200">
                @if(request('search') || request('prodi_id'))
                    <a href="{{ url()->current() }}" onclick="handleResetLoading()" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-circle-xmark"></i></a>
                @endif
            </div>

            <button type="submit" id="btnSubmitCari" class="w-full sm:w-auto bg-gradient-to-r from-slate-900 via-blue-900 to-red-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all shadow-blue-900/30">
                <i class="fa-solid fa-magnifying-glass mr-2"></i><span>Cari</span>
            </button>
        </form>
    </div>

    {{-- DASHBOARD CARD STATS SUMMARY --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-8">
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Antre</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Antre')) }}</h4></div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-spinner fa-spin"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Diproses</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Diproses')) }}</h4></div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-circle-check"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Selesai</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Selesai')) }}</h4></div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-circle-xmark"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Ditolak</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_layanan', 'Ditolak')) }}</h4></div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/40 text-red-500 flex items-center justify-center text-xl shadow-inner"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Terlambat</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan->where('status_sla', 'TERLAMBAT')) }}</h4></div>
        </div>
        <div class="bg-white dark:bg-slate-800 p-5 rounded-3xl border border-gray-100 dark:border-slate-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 flex items-center justify-center text-lg"><i class="fa-solid fa-layer-group"></i></div>
            <div><p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total</p><h4 class="text-xl font-black text-gray-800 dark:text-white">{{ count($data_kunjungan) }}</h4></div>
        </div>
    </div>

    {{-- CONTAINER TAB FILTER DENGAN ALPINE.JS --}}
    <div x-data="{ activeTab: 'all' }" class="w-full">
        
        {{-- TOMBOL TAB INTERNAL VS EKSTERNAL --}}
        <div class="flex gap-2 mb-6">
            <button @click="activeTab = 'all'" :class="activeTab === 'all' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">Semua</button>
            <button @click="activeTab = 'internal'" :class="activeTab === 'internal' ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">Internal</button>
            <button @click="activeTab = 'eksternal'" :class="activeTab === 'eksternal' ? 'bg-orange-600 text-white shadow-lg shadow-orange-600/30' : 'bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700'" class="px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider transition-all">Eksternal</button>
        </div>

        {{-- TABLE CONTAINER --}}
        <div class="bg-white dark:bg-slate-800 rounded-2xl md:rounded-[2.5rem] border border-gray-100 dark:border-slate-700 shadow-sm overflow-hidden transition-colors duration-300">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[1050px]">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-slate-900/40">
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nomor Antrean</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Pengunjung</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Kontak WA</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status Layanan</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Sisa Estimasi (Countdown)</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status SLA</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Tanggal</th>
                            <th class="px-6 md:px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 dark:divide-slate-700/50">
                        @forelse($data_kunjungan as $k)
                        @php 
                            $tipeTamu = $k->tipe_tamu ?? 'eksternal'; 
                            
                            // VALIDASI CASE-INSENSITIVE AGAR VERIFIKASI SURVEY TIDAK MELUSET
                            $statusLayananClean = strtoupper(trim($k->status_layanan ?? ''));
                            $isHutangSurvei = ($statusLayananClean === 'SELESAI' && empty($k->survey));
                            
                            $noWaRaw = $k->pengunjung->no_telepon ?? $k->no_telepon ?? '';
                            $noWa = preg_replace('/[^0-9]/', '', $noWaRaw);
                            if (str_starts_with($noWa, '0')) {
                                $noWa = '62' . substr($noWa, 1);
                            } elseif (!str_starts_with($noWa, '62') && !empty($noWa)) {
                                $noWa = '62' . $noWa;
                            }
                            $pesanWa = urlencode("Halo *" . ($k->pengunjung->nama_lengkap ?? 'Umum') . "* (No. Antrean: *" . $k->nomor_kunjungan . "*),\n\nUrusan pelayanan Anda di Jurusan Teknik Elektro telah Selesai. Mohon luangkan waktu 1 menit untuk mengisi survei kepuasan layanan pada tautan resmi berikut agar slot token antrean dapat terbuka kembali untuk pengunjung lain:\n\n" . route('survey.form', $k->nomor_kunjungan) . "\n\nTerima kasih atas kerja samanya!");
                        @endphp
                        
                        <tr x-show="activeTab === 'all' || activeTab === '{{ $tipeTamu }}'" x-cloak class="{{ $isHutangSurvei ? 'bg-amber-50/40 dark:bg-amber-950/10 hover:bg-amber-100/40' : 'hover:bg-slate-50/50 dark:hover:bg-slate-900/30' }} transition-colors group">
                            
                            <td class="px-6 md:px-8 py-4 md:py-6 font-bold text-gray-800 dark:text-slate-200 text-sm md:text-base">{{ $k->nomor_kunjungan }}</td>
                            
                            {{-- KOLOM NAMA PENGUNJUNG --}}
                            <td class="px-6 md:px-8 py-4 md:py-6">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-extrabold text-gray-800 dark:text-white text-sm md:text-base">{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}</p>
                                    <span class="px-2 py-0.5 text-[8px] font-black uppercase rounded {{ $tipeTamu == 'internal' ? 'bg-blue-100 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400' : 'bg-orange-100 dark:bg-orange-950/50 text-orange-600 dark:text-orange-400' }}">{{ $tipeTamu }}</span>
                                </div>
                                
                                <div class="mb-2 mt-1">
                                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Jenis</p>
                                    <p class="text-sm font-bold text-slate-700 dark:text-slate-300 italic leading-relaxed">{{ $k->keperluan_master->keterangan ?? '-' }}</p>
                                </div>
                                @if(!empty($k->keperluan) && $k->keperluan != '-')
                                    <div class="mb-2">
                                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Detail</p>
                                        <p class="text-sm font-medium text-slate-600 dark:text-slate-400 italic leading-relaxed">"{{ Str::limit($k->keperluan, 120) }}"</p>
                                    </div>
                                @endif
                                @if($k->catatan_pimpinan)
                                    <div class="mt-3 p-3 rounded-xl shadow-sm {{ $k->status_pimpinan == 'Setuju' ? 'bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50' : 'bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50' }}">
                                        <p class="text-[9px] font-black uppercase tracking-widest mb-1 {{ $k->status_pimpinan == 'Setuju' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}"><i class="fa-solid fa-comment-medical mr-1"></i> Respon Pimpinan : {{ $k->status_pimpinan }}</p>
                                        <p class="text-[11px] font-bold italic leading-relaxed {{ $k->status_pimpinan == 'Setuju' ? 'text-emerald-900 dark:text-emerald-300' : 'text-rose-900 dark:text-rose-300' }}">"{{ $k->catatan_pimpinan }}"</p>
                                    </div>
                                @endif
                                @if($statusLayananClean === 'SELESAI')
                                    <div class="mt-3 p-3 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 rounded-xl"><p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest">Durasi Pelayanan</p><p class="text-sm font-extrabold text-emerald-700 dark:text-emerald-300">{{ $k->durasi_layanan }}</p></div>
                                @elseif($statusLayananClean === 'DITOLAK')
                                    <div class="mt-3 p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/30 rounded-xl"><p class="text-[9px] font-black text-rose-600 uppercase tracking-widest">Alasan Penolakan</p><p class="text-xs font-bold text-rose-700 dark:text-rose-300 italic leading-relaxed">"{{ $k->alasan_tolak ?? 'Tidak ada alasan spesifik.' }}"</p></div>
                                @endif
                                @if($isHutangSurvei)
                                    <div class="mt-3 p-2 bg-rose-50 dark:bg-rose-950/20 border border-rose-100 dark:border-rose-900/40 rounded-xl text-[10px] font-black text-rose-600 dark:text-rose-400 uppercase tracking-wider animate-pulse inline-flex items-center gap-1"><i class="fa-solid fa-circle-exclamation text-xs"></i> Menunggu Ulasan</div>
                                @endif
                            </td>

                            {{-- TAMBAHAN: KOLOM BARU KHUSUS KONTAK WA --}}
                            <td class="px-6 md:px-8 py-4 md:py-6 text-center">
                                @if(!empty($noWa))
                                    <a href="https://wa.me/{{ $noWa }}" target="_blank" title="Hubungi via WhatsApp" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border border-emerald-200/40 dark:border-emerald-900/30 rounded-xl text-xs font-extrabold hover:bg-emerald-600 hover:text-white dark:hover:bg-emerald-500 hover:border-transparent transition-all shadow-sm group">
                                        <i class="fa-brands fa-whatsapp text-emerald-500 group-hover:text-white transition-colors text-sm"></i>
                                        <span>+{{ $noWa }}</span>
                                    </a>
                                @else
                                    <span class="text-gray-400 dark:text-slate-500 font-bold text-xs">-</span>
                                @endif
                            </td>
                            
                            <td class="px-6 md:px-8 py-4 md:py-6 text-center">
                                @php
                                    $color = match($statusLayananClean) {
                                        'SELESAI' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-950/50 dark:text-emerald-400',
                                        'DIPROSES' => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-950/50 dark:text-indigo-400',
                                        'DITOLAK' => 'bg-rose-100 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400',
                                        default => 'bg-amber-100 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'
                                    };
                                @endphp
                                <span class="px-3 md:px-4 py-1 {{ $color }} rounded-full text-[8px] md:text-[9px] font-black uppercase tracking-widest inline-block whitespace-nowrap">{{ $k->status_layanan }}</span>
                            </td>
                            
                            <td class="px-6 md:px-8 py-4 md:py-6 text-center font-bold text-sm">
                                @if($statusLayananClean === 'DIPROSES' && !empty($k->waktu_mulai_layanan))
                                    {{-- FIX UTAMA: PENYESUAIAN TIMER HARI KERJA & JAM OPERASIONAL DI SISI VIEW --}}
                                    @php
                                        $mulai = \Carbon\Carbon::parse($k->waktu_mulai_layanan, 'Asia/Makassar');
                                        $estimasi = (int) ($k->estimasi_sla ?? 0);
                                        $satuan = strtolower(trim($k->satuan_sla ?? 'menit'));
                                        
                                        // 1 Hari Kerja = 7 Jam Efektif (08:00 - 12:00 dan 13:00 - 16:00) = 420 Menit
                                        $menitTersisa = ($satuan === 'hari') ? ($estimasi * 420) : $estimasi;
                                        $target = $mulai->copy();
                                        
                                        // Menghitung batas waktu masa depan dengan melompati jam istirahat dan weekend
                                        while ($menitTersisa > 0) {
                                            $target->addMinute();
                                            $jamCheck = $target->format('H:i');
                                            $hariCheck = $target->dayOfWeekIso;
                                            
                                            $isJamKerja = (($jamCheck >= '08:00' && $jamCheck < '12:00') || ($jamCheck >= '13:00' && $jamCheck < '16:00'));
                                            $isHariKerja = ($hariCheck >= 1 && $hariCheck <= 5);
                                            
                                            if ($isHariKerja && $isJamKerja) {
                                                $menitTersisa--;
                                            }
                                        }
                                        $targetMs = $target->timestamp * 1000;
                                    @endphp
                                    <div class="live-timer" data-deadline="{{ $targetMs }}" data-type="sla">
                                        <span class="timer-badge px-3 py-1.5 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-xl text-[10px] font-black uppercase tracking-widest inline-flex items-center gap-1.5"><i class="fa-solid fa-stopwatch animate-spin"></i> <span class="timer-text">Menghitung...</span></span>
                                        <p class="text-[9px] text-slate-400 mt-1">Batas: {{ $target->translatedFormat('d M H:i') }} WITA</p>
                                    </div>
                                @elseif($statusLayananClean === 'ANTRE')
                                    @php
                                        $masuk = \Carbon\Carbon::parse($k->created_at, 'Asia/Makassar');
                                        $batasAntre = $masuk->copy()->addMinutes(10);
                                        $batasAntreMs = $batasAntre->timestamp * 1000;
                                    @endphp
                                    <div class="live-timer" data-deadline="{{ $batasAntreMs }}" data-type="antre">
                                        <span class="timer-badge px-3 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl text-[11px] font-black inline-flex items-center gap-1">⏳ Batas Respon: <span class="timer-text">Menghitung...</span></span>
                                    </div>
                                @else
                                    <span class="text-gray-400 dark:text-slate-500 font-bold text-xs">-</span>
                                @endif
                            </td>

                            <td class="px-8 py-6 text-center">
                                @php
                                    $status_sla = strtolower(trim($k->status_sla ?? ''));
                                @endphp
                                @if($statusLayananClean === 'SELESAI')
                                    @if($status_sla == 'tepat waktu')
                                        <span class="text-emerald-500 font-black text-[10px] flex items-center justify-center gap-1"><i class="fa-solid fa-circle-check"></i> TEPAT WAKTU</span>
                                    @elseif($status_sla == 'terlambat')
                                        <span class="text-rose-500 font-black text-[10px] flex items-center justify-center gap-1"><i class="fa-solid fa-circle-exclamation"></i> TERLAMBAT</span>
                                    @else
                                        <span class="text-gray-400 text-[10px] italic">Data SLA: "{{ $k->status_sla ?? 'Null/Kosong' }}"</span>
                                    @endif
                                @elseif($statusLayananClean === 'DITOLAK')
                                    <span class="text-rose-600 font-black text-[10px] flex items-center justify-center gap-1"><i class="fa-solid fa-ban"></i> GAGAL</span>
                                @else
                                    <span class="text-indigo-400 text-[9px] font-black uppercase italic tracking-tighter">Sedang Berjalan</span>
                                @endif
                            </td>

                            <td class="px-8 py-6 text-center">
                                <p class="text-gray-800 dark:text-slate-200 font-bold text-sm">{{ $k->created_at->format('d M Y') }}</p>
                                <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 mt-0.5">{{ $k->created_at->format('H:i') }} WITA</p>
                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mt-1">{{ $k->hari_kunjungan }}</p>
                            </td>

                            <td class="px-6 md:px-8 py-4 md:py-6 text-center">
                                <div class="flex justify-center gap-1.5 md:gap-2 items-center">
                                    <a href="{{ url('/status/'.$k->nomor_kunjungan) }}?view=admin" target="_blank" class="flex-shrink-0 w-8 h-8 md:w-9 md:h-9 flex items-center justify-center bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-slate-300 rounded-lg md:rounded-xl hover:bg-slate-800 dark:hover:bg-slate-900 hover:text-white transition-all shadow-sm"><i class="fa-solid fa-eye text-[10px] md:text-xs"></i></a>

                                    @if($user->role_id == 2)
                                        @if($statusLayananClean === 'ANTRE')
                                            <form action="{{ route('kunjungan.mulaiProses', $k->nomor_kunjungan) }}" method="POST" class="m-0" onsubmit="showGlobalLoading('Memulai antrean dan menetapkan estimasi otomatis...')">
                                                @csrf
                                                <button type="submit" class="w-9 h-9 flex items-center justify-center bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-xl hover:bg-indigo-600 dark:hover:bg-indigo-500 hover:text-white transition-all shadow-sm" title="Mulai Proses Otomatis"><i class="fa-solid fa-play text-xs"></i></button>
                                            </form>
<button type="button" onclick="bukaModalTolak('{{ $k->id }}', '{{ $k->nomor_kunjungan }}', '{{ addslashes($k->pengunjung->nama_lengkap ?? 'Umum') }}', '{{ $noWa }}')" class="w-9 h-9 flex items-center justify-center bg-rose-50 dark:bg-rose-950/30 text-rose-600 dark:text-rose-400 rounded-xl hover:bg-rose-600 dark:hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Tolak Antrean"><i class="fa-solid fa-xmark text-xs"></i></button>
                                        @elseif($statusLayananClean === 'DIPROSES')
                                            @if(!$k->is_email_sent)
                                                <button type="button" 
                                                    onclick="bukaModalEmail('{{ $k->id }}', '{{ addslashes($k->pengunjung->nama_lengkap ?? 'Umum') }}', '{{ addslashes($k->keperluan ?? '') }}', this)" 
                                                    data-nomor="{{ $k->nomor_kunjungan }}"
                                                    data-prodi="{{ $k->nama_prodi ?? '-' }}"
                                                    data-keperluan-utama="{{ $k->keperluan_master->keterangan ?? 'Kunjungan Umum' }}"
                                                    data-instansi="{{ $k->pengunjung->asal_instansi ?? 'Umum / Mandiri' }}"
                                                    class="w-9 h-9 flex items-center justify-center rounded-xl shadow-sm transition-all bg-blue-50 dark:bg-blue-950/30 text-blue-500 dark:text-blue-400 hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white" title="Kirim Email ke Pimpinan"><i class="fa-solid fa-envelope text-xs"></i></button>
                                            @endif
                                            @if(!$k->is_forwarded)
                                                <button type="button" onclick="bukaModalForward('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')" class="w-9 h-9 flex items-center justify-center rounded-xl shadow-sm transition-all bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 hover:bg-violet-600 dark:hover:bg-violet-500 hover:text-white" title="Teruskan ke Pimpinan"><i class="fa-solid fa-share-nodes text-xs"></i></button>
                                            @endif
                                            @if($k->is_forwarded && !$k->is_email_sent)
                                                <button type="button" onclick="peringatanEmailWajib('{{ $k->id }}', '{{ addslashes($k->pengunjung->nama_lengkap ?? 'Umum') }}', '{{ addslashes($k->keperluan ?? '-') }}')" class="w-9 h-9 flex items-center justify-center bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 rounded-xl hover:bg-amber-500 dark:hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Wajib Email Konfirmasi"><i class="fa-solid fa-triangle-exclamation text-xs"></i></button>
                                            @endif
                                            @if($k->is_email_sent)
                                                <button type="button" disabled class="w-9 h-9 flex items-center justify-center bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 rounded-xl cursor-not-allowed shadow-sm" title="Email Sudah Terkirim"><i class="fa-solid fa-envelope-circle-check text-xs"></i></button>
                                            @endif
                                            <form id="formSelesaiLayanan-{{ $k->id }}" action="{{ route('kunjungan.selesai', $k->id) }}" method="POST" class="m-0">
                                                @csrf
                                                <button type="button" onclick="konfirmasiSelesai('{{ $k->id }}')" class="w-9 h-9 flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Selesai"><i class="fa-solid fa-check text-xs"></i></button>
                                            </form>
                                            @if(empty($k->file_surat))
                                                <button type="button" onclick="bukaModalUpload('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')" class="w-9 h-9 flex items-center justify-center bg-amber-50 dark:bg-amber-950/30 text-amber-600 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Upload File"><i class="fa-solid fa-paperclip text-xs"></i></button>
                                            @endif
                                        @endif
                                    @endif
                                    @if($isHutangSurvei)
                                        <a href="https://wa.me/{{ $noWa }}?text={{ $pesanWa }}" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl shadow-md transition-all ring-4 ring-emerald-400/30 dark:ring-emerald-500/20 animate-pulse flex-shrink-0" title="Tagih Ulasan via WhatsApp"><i class="fa-brands fa-whatsapp text-sm font-bold"></i></a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        {{-- TAMBAHAN: COLSPAN DIUBAH MENJADI 8 --}}
                        <tr><td colspan="8" class="px-8 py-20 text-center text-gray-400 bg-white dark:bg-slate-800">Data tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL NOTIFIKASI PIMPINAN (EMAIL & WHATSAPP) --}}
    <div id="modalEmailPimpinan" class="fixed inset-0 z-[100] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-start bg-gray-50/50 dark:bg-gray-800/50 gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-black text-gray-800 dark:text-white flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-indigo-500 text-base"></i> 
                        Kirim Notifikasi Pimpinan
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 leading-relaxed">
                        <span class="text-amber-500 font-semibold">Saluran Fleksibel:</span> Harap isi salah satu saluran kontak (Email atau WhatsApp) or isi keduanya sekaligus.
                    </p>
                </div>
                <button type="button" id="btnCloseXEmail" onclick="tutupModalEmail()" class="text-gray-400 hover:text-rose-500 transition-colors shrink-0">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="formEmail" action="{{ route('kunjungan.kirim-email') }}" method="POST" class="p-6" onsubmit="validasiFormNotifikasi(event)">
                @csrf
                <input type="hidden" name="kunjungan_id" id="modal_kunjungan_id">
                
                <div class="mb-5 bg-indigo-50/50 dark:bg-indigo-950/30 p-4 rounded-2xl border border-indigo-100/50">
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Informasi Kunjungan</p>
                    <p class="font-bold text-gray-800 dark:text-gray-200 text-sm" id="modal_nama_pengunjung"></p>
                    <p class="text-xs text-gray-500 mt-1 italic" id="modal_keperluan_pengunjung"></p>
                </div>

                <div class="space-y-4 mb-6">
                    <div>
                        <label class="block block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Email Pimpinan</label>
                        <div class="relative">
                            <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                            <input type="email" name="email_pimpinan" id="email_pimpinan" placeholder="contoh: pimpinan@poliban.ac.id" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-medium">
                        </div>
                    </div>
                    <div>
                        <label class="block block text-[11px] font-bold text-gray-500 dark:text-gray-400 mb-2 uppercase tracking-widest">Nomor WhatsApp Pimpinan</label>
                        <div class="relative">
                            <i class="fa-brands fa-whatsapp absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500 text-sm"></i>
                            <input type="text" name="wa_pimpinan" id="wa_pimpinan" placeholder="Contoh: 08123456xxx atau 628123xxx" class="w-full pl-10 pr-4 py-3 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-indigo-500 outline-none transition-all font-medium">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" id="btnBatalEmail" onclick="tutupModalEmail()" class="px-5 py-2.5 text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">Batal</button>
                    <button type="submit" id="btnSubmitEmail" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all hover:scale-[1.01]"><i class="fa-solid fa-paper-plane text-xs"></i> Kirim Notifikasi</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL FORWARD PIMPINAN --}}
    <div id="modalForwardPimpinan" class="fixed inset-0 z-[120] hidden bg-black/40 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
                <div><h3 class="text-xl font-black text-gray-800 dark:text-white">Teruskan ke Pimpinan</h3><p class="text-xs text-gray-400 mt-1">Pilih tujuan disposisi layanan</p></div>
                <button type="button" id="btnCloseXForward" onclick="tutupModalForward()" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-rose-100 text-gray-400 hover:text-rose-500 transition-all"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <form id="formForwardPimpinan" action="{{ route('kunjungan.kirim-massal') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="ids[]" id="forward_kunjungan_id">
                <input type="hidden" name="nama_pengunjung" id="forward_nama_hidden">
                <input type="hidden" name="keperluan_pengunjung" id="forward_keperluan_hidden">
                <div class="mb-6"><div class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 rounded-2xl p-4"><p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 mb-1">Pengunjung</p><p id="forward_nama_pengunjung" class="font-bold text-gray-800 dark:text-gray-200 text-sm"></p></div></div>
                <div class="space-y-3 mb-8">
                    <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 transition-all cursor-pointer"><input type="radio" name="tujuan_pimpinan" value="kajur" required class="w-5 h-5 text-indigo-600"><div class="flex items-center gap-3"><div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center"><i class="fa-solid fa-user-tie"></i></div><div><p class="font-black text-gray-800 dark:text-gray-200 text-sm">Ketua Jurusan</p><p class="text-xs text-gray-400">Kirim ke Kajur Elektro</p></div></div></label>
                    <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 hover:border-violet-500 hover:bg-violet-50 transition-all cursor-pointer"><input type="radio" name="tujuan_pimpinan" value="kaprodi" required class="w-5 h-5 text-violet-600"><div class="flex items-center gap-3"><div class="w-12 h-12 rounded-2xl bg-violet-100 text-violet-600 flex items-center justify-center"><i class="fa-solid fa-user-graduate"></i></div><div><p class="font-black text-gray-800 dark:text-gray-200 text-sm">Ketua Program Studi</p><p class="text-xs text-gray-400">Kirim ke Kaprodi terkait</p></div></div></label>
                </div>
                <div class="flex gap-3">
                    <button type="button" id="btnBatalForward" onclick="tutupModalForward()" class="flex-1 py-3 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm">Batal</button>
                    <button type="button" id="btnSubmitForward" onclick="konfirmasiForward()" class="flex-1 py-3 rounded-2xl bg-violet-600 hover:bg-violet-700 text-white font-black text-sm shadow-lg">Teruskan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL UPLOAD FILE --}}
    <div id="modalUploadFile" class="fixed inset-0 z-[100] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up transition-colors duration-300">
            <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-amber-50/50 dark:bg-amber-950/20">
                <h3 class="text-lg font-black text-amber-800 dark:text-amber-400">Upload File Layanan</h3>
                <button type="button" id="btnCloseXUpload" onclick="tutupModalUpload()" class="text-gray-400 hover:text-rose-500 transition-colors"><i class="fa-solid fa-xmark text-lg"></i></button>
            </div>
            <form id="formUploadSelesai" method="POST" enctype="multipart/form-data" class="p-6" onsubmit="handleModalLoading(event, 'formUploadSelesai', 'btnSubmitUpload', 'btnBatalUpload', 'btnCloseXUpload')">
                @csrf
                <div class="mb-5 bg-amber-50 dark:bg-amber-950/30 p-4 rounded-2xl border border-amber-100"><p class="font-bold text-gray-800 dark:text-gray-200 text-sm" id="upload_nama_pengunjung"></p><p class="text-xs text-amber-700 dark:text-amber-300 mt-2 leading-relaxed">Upload dokumen pendukung dalam format <span class="font-bold">PDF, Word, atau Gambar(JPG, PNG, JPEG)</span> maksimal <span class="font-bold text-amber-600">10 MB</span>.</p></div>
                <div class="mb-6"><input type="file" name="file_surat" id="inputFileSurat" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" required class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:bg-amber-100 file:text-amber-700"><p id="errorSizeFile" class="hidden mt-2 text-xs text-rose-500 font-bold"></p></div>
                <div class="flex justify-end gap-3"><button type="button" id="btnBatalUpload" onclick="tutupModalUpload()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 dark:bg-gray-700 rounded-xl">Batal</button><button type="submit" id="btnSubmitUpload" class="hidden px-5 py-2.5 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-lg">Upload File</button></div>
            </form>
        </div>
    </div>

    {{-- MODAL TOLAK ANTREAN --}}
    <div id="modalTolak" class="fixed inset-0 z-[999] hidden items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[2rem] p-6 shadow-2xl border dark:border-slate-800">
            <div class="mb-5"><h2 class="text-xl font-black text-slate-900 dark:text-white">Tolak Antrean</h2><p class="text-sm text-slate-400 mt-1">Wajib isi alasan penolakan</p></div>
            <form id="formTolak" method="POST" action="" onsubmit="handleTolakLoading(event)" data-wa="" data-nama="" data-nomor="">
                @csrf
                <textarea id="txtAlasanTolak" name="alasan_tolak" required class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-rose-100 outline-none" placeholder="Contoh: Dokumen tidak lengkap / data tidak valid"></textarea>
                <div class="flex gap-3 mt-5"><button type="button" id="btnBatalTolak" onclick="tutupModalTolak()" class="flex-1 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 font-black text-xs uppercase">Batal</button><button type="submit" id="btnSubmitTolak" class="flex-1 py-3 rounded-2xl bg-rose-600 text-white font-black text-xs uppercase shadow-lg">Kirim Penolakan</button></div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div id="custom-loading-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-full text-center animate-modal-up">
        <div class="relative w-12 h-12 flex items-center justify-center mb-4">
            <div class="absolute inset-0 border-4 border-slate-100 dark:border-slate-900 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-red-600 dark:border-t-amber-400 rounded-full animate-spin"></div>
        </div>
        <h3 id="loading-title" class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Memproses Data</h3>
        <p id="loading-text" class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">Sedang menyinkronkan data, mohon tunggu...</p>
    </div>
</div>

<script>
    let isModalOpen = false;
    let currentDataInput = {};

    function showGlobalLoading(pesanText = "Sedang memproses data, mohon tunggu...") {
        const modal = document.getElementById('custom-loading-modal');
        const textEl = document.getElementById('loading-text');
        textEl.innerText = pesanText;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function getSwalConfig() {
        const isDark = document.documentElement.classList.contains('dark');
        return {
            background: isDark ? '#1e293b' : '#ffffff',
            color: isDark ? '#f8fafc' : '#1f2937',
            customClass: {
                popup: 'rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-xl',
                title: 'font-black tracking-tight text-xl pt-2',
                confirmButton: 'rounded-xl font-bold px-6 py-2.5 text-sm',
                cancelButton: 'rounded-xl font-bold px-6 py-2.5 text-sm'
            }
        };
    }

    function handleModalLoading(event, formId, ...buttonIds) {
        const form = document.getElementById(formId);
        if (!form.checkValidity()) { form.reportValidity(); return false; }
        buttonIds.forEach(id => { const btn = document.getElementById(id); if (btn) btn.disabled = true; });
        showGlobalLoading("Memproses data...");
        form.submit();
    }

    function bukaModalTolak(id, nomorKunjungan, namaPengunjung, noWa) {
        isModalOpen = true; 
        const modal = document.getElementById('modalTolak'); 
        const form = document.getElementById('formTolak');
        
        // Data langsung dimasukkan ke dalam atribut form tanpa perlu membongkar elemen HTML tabel
        form.action = `/dashboard/tolak/${id}`; 
        form.setAttribute('data-wa', noWa || '');
        form.setAttribute('data-nama', namaPengunjung || 'Umum');
        form.setAttribute('data-nomor', nomorKunjungan || '-');

        document.getElementById('txtAlasanTolak').value = '';
        modal.classList.remove('hidden'); 
        modal.classList.add('flex'); 
    }

    // Perbaikan Bug: melepaskan isModalOpen saat menutup modal tolak
    function tutupModalTolak() {
        document.getElementById('modalTolak').classList.add('hidden');
        document.getElementById('modalTolak').classList.remove('flex');
        isModalOpen = false;
    }

    function handleTolakLoading(event) {
        event.preventDefault(); // Cegah reload halaman default
        
        const form = document.getElementById('formTolak');
        const btnSubmit = document.getElementById('btnSubmitTolak');
        const alasan = document.getElementById('txtAlasanTolak').value.trim();
        
        const noWa = form.getAttribute('data-wa');
        const nama = form.getAttribute('data-nama');
        const nomorKunjungan = form.getAttribute('data-nomor');

        if (!alasan) return;

        btnSubmit.disabled = true;
        showGlobalLoading("Memproses penolakan antrean..."); //[cite: 10]

        // 1. Kirim data ke backend menggunakan Fetch API (AJAX) agar status di Spreadsheet berubah dahulu
        fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Tutup modal loading global & modal input alasan
            document.getElementById('custom-loading-modal').classList.add('hidden'); //[cite: 10]
            tutupModalTolak(); //[cite: 10]

            // Susun template pesan teks WhatsApp penolakan yang rapi
            const pesanWa = `*PEMBERITAHUAN PENOLAKAN ANTREAN*\n\n` +
                            `Halo *${nama}*,\n` +
                            `Mohon maaf, permohonan pelayanan Anda dengan Nomor Antrean *${nomorKunjungan}* terpaksa kami tolak dengan alasan sebagai berikut:\n\n` +
                            `*ALASAN PENOLAKAN:*\n` +
                            `"${alasan}"\n\n` +
                            `Silakan lakukan perbaikan berkas atau sesuaikan kembali permohonan Anda sebelum mengambil antrean baru.\n\n` +
                            `-----------------------------------------\n` +
                            `_*Pesan Otomatis Loket - Jurusan Teknik Elektro Poliban*_`;

            const waUrl = `https://wa.me/${noWa}?text=${encodeURIComponent(pesanWa)}`;

            // 2. KUNCI TOTAL ADMIN MENGGUNAKAN SWEETALERT2 (Wajib Klik Kirim WA)
            Swal.fire({
                ...getSwalConfig(), //[cite: 10]
                icon: 'warning',
                title: 'Wajib Hubungi Pengunjung!',
                text: 'Status berhasil diperbarui di sistem. Anda wajib mengirimkan alasan penolakan ini ke WhatsApp pengunjung agar mereka mengetahuinya.',
                confirmButtonText: '<i class="fa-brands fa-whatsapp mr-2"></i> Kirim Alasan via WA',
                confirmButtonColor: '#10b981',
                allowOutsideClick: false, // Tidak bisa ditutup dengan klik luar pop-up
                allowEscapeKey: false,    // Tidak bisa ditutup dengan tombol ESC keyboard
                allowEnterKey: true,
                showConfirmButton: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buka tab WhatsApp
                    window.open(waUrl, '_blank');
                    
                    // Segarkan halaman utama untuk menyinkronkan tabel
                    window.location.reload();
                }
            });
        })
        .catch(error => {
            document.getElementById('custom-loading-modal').classList.add('hidden'); //[cite: 10]
            btnSubmit.disabled = false;
            Swal.fire({
                ...getSwalConfig(), //[cite: 10]
                icon: 'error',
                title: 'Koneksi Gagal',
                text: 'Terjadi kesalahan sistem saat mencoba memperbarui status. Silakan coba kembali.'
            });
        });
    }

    function bukaModalEmail(id, nama, keperluan, btnElement) {
        isModalOpen = true;
        document.getElementById('modal_kunjungan_id').value = id;
        document.getElementById('modal_nama_pengunjung').innerText = nama;
        document.getElementById('modal_keperluan_pengunjung').innerText = keperluan ? `"${keperluan}"` : '-';
        
        if (btnElement) {
            currentDataInput = {
                nomor_kunjungan: btnElement.getAttribute('data-nomor') || '-',
                nama_pengunjung: nama,
                keperluan_detail: keperluan || '-',
                prodi: btnElement.getAttribute('data-prodi') || '-',
                keperluan_utama: btnElement.getAttribute('data-keperluan-utama') || 'Kunjungan Umum',
                instansi: btnElement.getAttribute('data-instansi') || 'Umum / Mandiri'
            };
        }
        document.getElementById('modalEmailPimpinan').classList.remove('hidden');
    }

    function tutupModalEmail() { 
        document.getElementById('modalEmailPimpinan').classList.add('hidden'); 
        isModalOpen = false; 
    }

    function validasiFormNotifikasi(event) {
        event.preventDefault();
        const emailInput = document.getElementById('email_pimpinan').value.trim();
        let waInput = document.getElementById('wa_pimpinan').value.trim();

        if (!emailInput && !waInput) {
            Swal.fire({
                ...getSwalConfig(),
                icon: 'warning',
                title: 'Kontak Kosong',
                text: 'Harap isi salah satu saluran: Masukkan Email atau Nomor WhatsApp Pimpinan!',
                confirmButtonColor: '#4f46e5'
            });
            return false;
        }

        if (waInput) {
            let noWa = waInput.replace(/[^0-9]/g, '');
            if (noWa.startsWith('0')) {
                noWa = '62' + noWa.substring(1);
            } else if (!noWa.startsWith('62') && noWa !== '') {
                noWa = '62' + noWa;
            }

            const baseUrl = window.location.origin;
            const tautanKonfirmasi = `${baseUrl}/dashboard/pimpinan/konfirmasi`;
            
            // PROTEKSI UNDEFINED: Gunakan fallback string kosong ('') atau tanda strip ('-')
            const noKunjungan = currentDataInput.nomor_kunjungan || '-';
            const namaTamu    = currentDataInput.nama_pengunjung || 'Umum';
            const asalInstansi= currentDataInput.instansi || '-';
            const prodiTujuan = currentDataInput.prodi || '-';
            const kepUtama    = currentDataInput.keperluan_utama || '-';
            const kepDetail   = currentDataInput.keperluan_detail || '-';

            const pesanWa = `*NOTIFIKASI LAYANAN PUBLIK ELEKTRO*\n\n` +
                            `Halo, *Bapak/Ibu Pimpinan*\n\n` +
                            `Terdapat permintaan persetujuan atau konfirmasi antrean kunjungan baru yang diteruskan kepada Anda. Berikut rincian datanya:\n\n` +
                            `*RINCIAN DATA ANTREAN:*\n` +
                            `• *Nomor Kunjungan:* ${noKunjungan}\n` +
                            `• *Nama Pengunjung:* ${namaTamu}\n` +
                            `• *Asal Instansi:* ${asalInstansi}\n` +
                            `• *Program Studi Terkait:* ${prodiTujuan}\n\n` +
                            `*KEPERLUAN & DETAIL LAYANAN:*\n` +
                            `• *Layanan Utama:* ${kepUtama}\n` +
                            `• *Keterangan Detail:* "${kepDetail}"\n\n` +
                            `Silakan berikan tanggapan, instruksi, atau konfirmasi Anda secara langsung dengan mengakses tautan di bawah ini:\n\n` +
                            `*Beri Tanggapan Sekarang:*\n` +
                            `${tautanKonfirmasi}\n\n` +
                            `-----------------------------------------\n` +
                            `_*Notifikasi Otomatis - Jurusan Teknik Elektro Poliban*_`;

            window.open(`https://wa.me/${noWa}?text=${encodeURIComponent(pesanWa)}`, '_blank');
        }

        if (emailInput) {
            handleModalLoading(event, 'formEmail', 'btnSubmitEmail', 'btnBatalEmail', 'btnCloseXEmail');
        } else {
            tutupModalEmail();
            Swal.fire({
                ...getSwalConfig(),
                icon: 'success',
                title: 'Berhasil',
                text: 'Tautan chat WhatsApp pimpinan berhasil dibuka!',
                confirmButtonColor: '#10b981'
            });
        }
    }

    function bukaModalUpload(id, nama) {
        isModalOpen = true;
        document.getElementById('upload_nama_pengunjung').innerText = nama;
        document.getElementById('formUploadSelesai').action = `/dashboard/upload-file/${id}`;
        document.getElementById('modalUploadFile').classList.remove('hidden');
    }

    function tutupModalUpload() { document.getElementById('modalUploadFile').classList.add('hidden'); isModalOpen = false; }

    function bukaModalForward(id, nama, keperluan) {
        isModalOpen = true;
        document.getElementById('forward_kunjungan_id').value = id;
        document.getElementById('forward_nama_pengunjung').innerText = nama;
        if(document.getElementById('forward_nama_hidden')) document.getElementById('forward_nama_hidden').value = nama;
        if(document.getElementById('forward_keperluan_hidden')) document.getElementById('forward_keperluan_hidden').value = keperluan || '';
        document.getElementById('modalForwardPimpinan').classList.remove('hidden');
    }

    function tutupModalForward() { document.getElementById('modalForwardPimpinan').classList.add('hidden'); isModalOpen = false; }

    function konfirmasiForward() {
        const form = document.getElementById('formForwardPimpinan');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        Swal.fire({
            ...getSwalConfig(),
            title: 'Teruskan ke Pimpinan?',
            text: 'Data disposisi layanan ini akan segera dikirimkan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Teruskan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#7c3aed'
        }).then((result) => {
            if (result.isConfirmed) {
                showGlobalLoading("Sedang merujuk data kunjungan ke pimpinan...");
                form.submit();
            }
        });
    }

    function konfirmasiSelesai(id) {
        const form = document.getElementById(`formSelesaiLayanan-${id}`);
        if (!form) return;
        Swal.fire({
            ...getSwalConfig(),
            title: 'Selesaikan Layanan?',
            text: 'Pastikan seluruh proses pelayanan kunjungan ini telah selesai dikerjakan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Selesai',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                showGlobalLoading("Memperbarui status ke selesai...");
                form.submit();
            }
        });
    }

    // Mengamankan penutupan modal loading
    function handleCariLoading(event, formElement) { showGlobalLoading("Mencari data..."); return true; }
    function handleSelectProdiLoading(selectElement) { showGlobalLoading("Memfilter prodi..."); selectElement.form.submit(); }
    function handleResetLoading() { showGlobalLoading("Memuat ulang data..."); }

    function peringatanEmailWajib(id, nama, keperluan) {
        Swal.fire({
            ...getSwalConfig(),
            title: 'Email Belum Terkirim!',
            text: `Data "${nama}" sudah diteruskan, namun notifikasi belum dikirim.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Buka Form Notifikasi',
            confirmButtonColor: '#f59e0b'
        }).then((result) => { 
            if (result.isConfirmed) {
                bukaModalEmail(id, nama, keperluan, null); 
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        const inputFile = document.getElementById('inputFileSurat');
        const btnSubmitUpload = document.getElementById('btnSubmitUpload');
        const errorSizeFile = document.getElementById('errorSizeFile');

        if (inputFile && btnSubmitUpload) {
            inputFile.addEventListener('change', function () {
                if (this.files[0] && this.files[0].size > 10 * 1024 * 1024) {
                    errorSizeFile.innerText = "⚠️ Ukuran berkas maksimal 10 MB!";
                    errorSizeFile.classList.remove('hidden');
                    this.value = ""; btnSubmitUpload.classList.add('hidden');
                } else {
                    errorSizeFile.classList.add('hidden'); btnSubmitUpload.classList.remove('hidden');
                }
            });
        }
        
        @if(session('success_upload_remind'))
            Swal.fire({ ...getSwalConfig(), title: 'Berhasil!', text: "{{ session('success_upload_remind') }}", icon: 'success' });
        @endif
        @if(session('trigger_email_modal'))
            bukaModalEmail("{{ session('email_kunjungan_id') }}", "{{ session('email_nama') }}", {!! json_encode(session('email_keperluan')) !!}, null);
        @endif
    });

    // Melakukan auto-reload halaman hanya jika modal ulasan/forward sedang tidak terbuka
    setInterval(() => { if (!isModalOpen && !Swal.isVisible()) window.location.reload(); }, 180000);

    {{-- FIX KEDUA: PENYESUAIAN FORMAT DISPLAY JAVASCRIPT TIMER AGAR MENDUKUNG PECAHAN HARI --}}
    function startLiveTimers() {
        const timers = document.querySelectorAll('.live-timer');
        setInterval(() => {
            const now = new Date().getTime();
            timers.forEach(timer => {
                const deadline = parseInt(timer.getAttribute('data-deadline'));
                const type = timer.getAttribute('data-type');
                const textEl = timer.querySelector('.timer-text');
                const badgeEl = timer.querySelector('.timer-badge');
                if (!deadline || !textEl) return;
                const diff = deadline - now;
                if (diff <= 0) {
                    textEl.innerText = type === 'antre' ? "Habis" : "Terlambat";
                    if (badgeEl) badgeEl.className = "timer-badge px-3 py-1 bg-rose-500/10 text-rose-600 rounded-xl text-[10px] font-black uppercase tracking-widest animate-pulse";
                    return;
                }
                
                const s = Math.floor(diff / 1000);
                const m = Math.floor(s / 60);
                const h = Math.floor(m / 60);
                const d = Math.floor(h / 24);
                
                let displayString = "";
                if (d > 0) {
                    displayString = d + "h " + (h % 24) + "j " + (m % 60).toString().padStart(2, '0') + "m";
                } else if (h > 0) {
                    displayString = h + "j " + (m % 60).toString().padStart(2, '0') + "m";
                } else {
                    displayString = (m % 60).toString().padStart(2, '0') + ":" + (s % 60).toString().padStart(2, '0');
                }
                
                textEl.innerText = displayString;
            });
        }, 1000);
    }
    startLiveTimers();
</script>
@endsection