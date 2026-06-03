<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Layanan - {{ $kunjungan->nomor_kunjungan }}</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        // 1. Konfigurasi Tailwind untuk menggunakan class 'dark'
        tailwind.config = {
            darkMode: 'class',
        }

        // 2. Skrip Cek Otomatis: Langsung eksekusi sebelum sisa HTML selesai dimuat agar tidak berkedip
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .gradient-red { background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); }
        .timeline-line { position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; }
        .animate-bounce-slow { animation: bounce 3s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(-5%); } 50% { transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 bg-[#F1F5F9] text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

    <div class="w-full max-w-sm sm:max-w-md space-y-4 sm:space-y-6 my-4">

        {{-- TOMBOL TOGGLE DARK MODE --}}
        <div class="flex justify-end">
            {{-- BUTTON TOGGLE DARK MODE (Sudah Diperbaiki Warna Kuningnya) --}}
            <button id="theme-toggle" class="p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl sm:rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 hover:scale-105 active:scale-95 transition-all">
                {{-- Ikon Bulan (Muncul saat Light Mode, siap diklik untuk ke Dark Mode) --}}
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                {{-- Ikon Matahari (Muncul saat Dark Mode aktif, berwarna Kuning Amber cerah) --}}
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-amber-500 animate-[spin_4s_linear_infinite]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zm-.707-4.95a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm3.182-5.657a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
            </button>
        </div>

        {{-- CARD UTAMA --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300">
            <div class="{{ $kunjungan->status_layanan == 'Ditolak' ? 'gradient-red' : 'gradient-bg' }} p-6 sm:p-8 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl"></div>

                <div class="w-14 h-14 sm:w-16 sm:h-16 bg-white/20 rounded-full mx-auto flex items-center justify-center mb-4 backdrop-blur-md border border-white/30 shadow-inner">
                    @if($kunjungan->status_layanan == 'Selesai')
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif($kunjungan->status_layanan == 'Ditolak')
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>

                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2 uppercase break-words px-2">{{ $kunjungan->status_layanan }}</h1>
                <p class="text-white/70 text-[11px] font-medium mb-4 sm:mb-6 tracking-wide">Nomor Antrean Anda</p>

                <div class="bg-white/10 backdrop-blur-md py-3 sm:py-4 px-2 rounded-2xl sm:rounded-3xl border border-white/20">
                    <p class="text-4xl sm:text-[3.5rem] font-black tracking-tighter leading-none mb-1 break-all">{{ $kunjungan->nomor_kunjungan }}</p>
                </div>

                {{-- INFO ESTIMASI SLA --}}
                @if($kunjungan->status_layanan == 'Diproses' && $kunjungan->estimasi_sla)
                    <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl border border-white/30 animate-bounce-slow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/80 mb-1">Estimasi Waktu Tunggu</p>
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 sm:w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-lg sm:text-xl font-black text-white break-words">
                                {{ $kunjungan->estimasi_sla }} {{ $kunjungan->satuan_sla }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- DURASI PELAYANAN --}}
            @if($kunjungan->status_layanan == 'Selesai')
                <div class="p-5 sm:p-6 bg-emerald-50/40 dark:bg-emerald-950/20 border-b border-emerald-100 dark:border-emerald-900 flex flex-row items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Durasi Pelayanan</p>
                        <p class="text-base sm:text-lg font-extrabold text-emerald-700 dark:text-emerald-300 truncate">
                            @php
                                $waktuMulai = $kunjungan->waktu_mulai_layanan ? \Carbon\Carbon::parse($kunjungan->waktu_mulai_layanan) : null;
                                $waktuAkhir = $kunjungan->waktu_selesai_layanan ? \Carbon\Carbon::parse($kunjungan->waktu_selesai_layanan) : $kunjungan->updated_at;
                                
                                if ($waktuMulai) {
                                    $totalDetik = $waktuMulai->diffInSeconds($waktuAkhir);
                                    $jam = floor($totalDetik / 3600);
                                    $menit = floor(($totalDetik % 3600) / 60);
                                    $detik = $totalDetik % 60;
                                    
                                    if ($jam > 0) { $durasi = "{$jam} Jam {$menit} Mnt"; }
                                    elseif ($menit > 0) { $durasi = "{$menit} Mnt {$detik} Dtk"; }
                                    else { $durasi = "{$detik} Detik"; }
                                } else {
                                    $durasi = "Data Invalid"; 
                                }
                            @endphp
                            {{ $durasi }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 px-3 sm:px-4 py-1.5 bg-emerald-500 text-white text-[10px] font-black rounded-full uppercase tracking-tighter shadow-sm">
                        Selesai
                    </div>
                </div>

                {{-- CARD DOWNLOAD FILE PDF --}}
                @if($kunjungan->file_surat)
                <div class="p-5 sm:p-6 bg-white dark:bg-slate-900 border-b border-slate-50 dark:border-slate-800 flex flex-col items-center text-center">
                    <div class="w-11 h-11 sm:w-12 sm:h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-3">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight">Dokumen Hasil Layanan</h3>
                    <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4 px-2 leading-relaxed">Dokumen balasan Anda telah tersedia. Silakan klik tombol di bawah untuk mengunduh.</p>

                    <a href="{{ asset('storage/surat/' . $kunjungan->file_surat) }}"
                       target="_blank"
                       class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-500 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200 dark:shadow-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Berkas (PDF)
                    </a>
                </div>
                @endif
            @endif

            <div class="px-6 sm:px-8 py-4 sm:py-6 flex justify-between gap-4 border-b border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                <div class="min-w-0 flex-1">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Pengunjung</p>
                    <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 truncate">{{ $kunjungan->pengunjung->nama_lengkap }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Waktu Terbit</p>
                    <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">{{ $kunjungan->created_at->format('H:i') }} WITA</p>
                </div>
            </div>

            {{-- KEPERLUAN --}}
            <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900">
                <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl sm:rounded-2xl p-4 border border-slate-100 dark:border-slate-800">
                    <p class="text-[10px] uppercase font-black tracking-widest text-indigo-500 dark:text-indigo-400 mb-3">
                        Keperluan
                    </p>
                    <div class="mb-3">
                        <p class="text-[9px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest mb-1">Jenis</p>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 italic leading-relaxed break-words">
                            {{ $kunjungan->keperluan_master->keterangan ?? '-' }}
                        </p>
                    </div>
                    @if(!empty($kunjungan->keperluan))
                    <div>
                        <p class="text-[9px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest mb-1">Detail</p>
                        <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed italic break-words">
                            "{{ $kunjungan->keperluan }}"
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- TANGGAPAN PIMPINAN --}}
        @if($kunjungan->catatan_pimpinan)
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300">
            <div class="flex items-center gap-2 mb-4 sm:mb-6">
                <div class="w-2 h-5 bg-indigo-500 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest">Pesan dari Pimpinan</h2>
            </div>
            <div class="p-4 sm:p-5 rounded-2xl sm:rounded-3xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/50">
                @if($kunjungan->status_pimpinan && $kunjungan->status_pimpinan != 'Menunggu')
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter bg-indigo-600 text-white">
                            {{ $kunjungan->status_pimpinan }}
                        </span>
                    </div>
                @endif
                <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Catatan Pimpinan:</p>
                <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 italic break-words">
                    "{{ $kunjungan->catatan_pimpinan }}"
                </p>
            </div>
        </div>
        @endif

        {{-- RIWAYAT STATUS --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300">
            <div class="flex items-center gap-2 mb-6 sm:mb-8">
                <div class="w-2 h-5 bg-indigo-600 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest">Riwayat Status</h2>
            </div>
            
            @if($kunjungan->alasan_tolak)
            <div class="mt-4 mb-6 relative bg-white/80 dark:bg-slate-800/50 border border-rose-200 dark:border-rose-900/50 rounded-xl sm:rounded-2xl p-4 shadow-sm overflow-hidden">
                <div class="absolute -top-6 -right-6 w-16 h-16 bg-rose-100 dark:bg-rose-900/20 rounded-full blur-xl"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-rose-500 mb-2">Alasan Penolakan</p>
                <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-400 font-medium leading-relaxed break-words">{{ $kunjungan->alasan_tolak }}</p>
            </div>
            @endif

            <div class="relative space-y-8 sm:space-y-10">
                <div class="timeline-line bg-slate-100 dark:bg-slate-800"></div>

                {{-- STATUS 3: SELESAI --}}
                <div class="relative pl-8 sm:pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 flex items-center justify-center
                        {{ $kunjungan->status_layanan == 'Selesai' ? 'bg-emerald-500 shadow-lg shadow-emerald-100 dark:shadow-none' : 'bg-slate-200 dark:bg-slate-700' }}">
                        @if($kunjungan->status_layanan == 'Selesai')
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold {{ $kunjungan->status_layanan == 'Selesai' ? 'text-slate-900 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600' }}">Layanan Selesai</h4>
                    <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                        {{ $kunjungan->status_layanan == 'Selesai' ? 'Tuntas pada ' . ($kunjungan->waktu_selesai_layanan ? Carbon\Carbon::parse($kunjungan->waktu_selesai_layanan)->format('H:i') : $kunjungan->updated_at->format('H:i')) . ' WITA' : 'Menunggu penyelesaian' }}
                    </p>
                </div>

                {{-- STATUS 2: DIPROSES --}}
                <div class="relative pl-8 sm:pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 flex items-center justify-center
                        {{ in_array($kunjungan->status_layanan, ['Diproses', 'Selesai']) ? 'bg-blue-500 shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-slate-200 dark:bg-slate-700' }}">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold {{ in_array($kunjungan->status_layanan, ['Diproses', 'Selesai']) ? 'text-slate-900 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600' }}">Sedang Diproses</h4>
                    @if($kunjungan->status_layanan == 'Diproses')
                        <p class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400">Petugas sedang melayani Anda</p>
                    @else
                        <p class="text-[11px] font-medium text-slate-400 dark:text-slate-500">Petugas memproses keperluan Anda</p>
                    @endif
                </div>

                {{-- STATUS 1: TERDAFTAR --}}
                <div class="relative pl-8 sm:pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 bg-amber-400 shadow-lg shadow-amber-100 dark:shadow-none flex items-center justify-center">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold text-slate-900 dark:text-slate-100">Tiket Terdaftar</h4>
                    <p class="text-[11px] font-bold text-amber-600 dark:text-amber-400">Pukul {{ $kunjungan->created_at->format('H:i') }} WITA</p>
                </div>
            </div>
        </div>

        {{-- AKSI --}}
        <div class="pt-2 space-y-4">
            @if($kunjungan->status_layanan == 'Antre')
                <a href="{{ url('/') }}" class="w-full flex items-center justify-center py-4 bg-slate-900 dark:bg-slate-800 text-white font-extrabold rounded-2xl sm:rounded-3xl shadow-xl hover:bg-black dark:hover:bg-slate-700 transition-all gap-3 text-sm">
                    <i class="fa-solid fa-house"></i>
                    <span>Kembali ke Beranda</span>
                </a>

            @elseif($kunjungan->status_layanan == 'Diproses')
                <div class="w-full flex items-center justify-center py-4 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-2xl sm:rounded-3xl border border-indigo-100 dark:border-indigo-900/50">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span class="font-black uppercase tracking-widest text-[11px] sm:text-xs">Layanan Sedang Diproses</span>
                    </div>
                </div>

            @elseif($kunjungan->status_layanan == 'Ditolak')
                <div class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900/50 rounded-2xl sm:rounded-3xl p-5 text-center">
                    <p class="text-rose-700 dark:text-rose-400 font-black text-sm uppercase tracking-wide">Permohonan Ditolak</p>
                    <p class="text-[12px] text-rose-600 dark:text-rose-500 mt-2 leading-relaxed">Mohon periksa kembali persyaratan atau hubungi petugas.</p>
                </div>
                <a href="{{ url('/') }}" class="w-full flex items-center justify-center py-4 bg-slate-900 dark:bg-slate-800 text-white font-extrabold rounded-2xl sm:rounded-3xl shadow-xl hover:bg-black dark:hover:bg-slate-700 transition-all gap-3 text-sm">
                    <i class="fa-solid fa-house"></i>
                    <span>Kembali ke Beranda</span>
                </a>

            @elseif($kunjungan->status_layanan == 'Selesai')
                @if(!$kunjungan->survey)
                    <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50 rounded-2xl sm:rounded-3xl p-5 text-center">
                        <p class="text-amber-700 dark:text-amber-400 font-black text-sm uppercase tracking-wide">Survei Layanan Wajib Diisi</p>
                        <p class="text-[12px] text-amber-600 dark:text-amber-500 mt-2 leading-relaxed">Silakan isi survei terlebih dahulu sebelum meninggalkan halaman ini.</p>
                    </div>
                    <a href="{{ route('survey.form', $kunjungan->nomor_kunjungan) }}" class="w-full flex items-center justify-center py-4 bg-indigo-600 text-white font-extrabold rounded-2xl sm:rounded-3xl shadow-xl hover:bg-indigo-700 transition-all active:scale-95 gap-3 text-sm">
                        <i class="fa-solid fa-star"></i>
                        <span>Isi Survei Layanan</span>
                    </a>
                @else
                    <div class="w-full flex items-center justify-center py-4 px-3 bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 font-bold rounded-2xl sm:rounded-3xl border border-emerald-200 dark:border-emerald-900/50 text-xs sm:text-sm text-center">
                        <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Ulasan berhasil dikirim. Terima kasih!</span>
                    </div>
                    <a href="{{ url('/') }}" class="w-full flex items-center justify-center py-4 bg-slate-900 dark:bg-slate-800 text-white font-extrabold rounded-2xl sm:rounded-3xl shadow-xl hover:bg-black dark:hover:bg-slate-700 transition-all gap-3 text-sm">
                        <i class="fa-solid fa-house"></i>
                        <span>Kembali ke Beranda</span>
                    </a>
                @endif
            @endif
        </div>

        <p class="text-center text-[10px] font-black text-slate-300 dark:text-slate-700 uppercase tracking-[0.6em] pt-2">Digital Gate System</p>
    </div>

    {{-- AUTO RELOAD JIKA BELUM SELESAI --}}
    @if(!in_array($kunjungan->status_layanan, ['Selesai', 'Ditolak']))
    <script>
        setTimeout(function(){ window.location.reload(1); }, 15000);
    </script>
    @endif

    {{-- SCRIPT INTERAKSI TOMBOL TOGGLE --}}
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            } else {
                darkIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            }
        }

        updateIcons();

        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcons();
        });
    </script>
</body>
</html>