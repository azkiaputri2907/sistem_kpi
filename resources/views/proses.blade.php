<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Layanan - {{ $kunjungan->nomor_kunjungan }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <script>
        tailwind.config = {
            darkMode: 'class',
        }

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .gradient-bg { background: linear-gradient(135deg, #0b3a82 0%, #072a63 100%); }
        .gradient-red { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
        .timeline-line { position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; }
        .animate-bounce-slow { animation: bounce 3s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(-5%); } 50% { transform: translateY(0); } }
        .animate-popup { animation: popIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.9) translateY(10px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 bg-[#F1F5F9] text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

@php
    $isAdmin = (request()->get('view') == 'admin');
    $statusLayananClean = strtoupper(trim($kunjungan->status_layanan ?? ''));
    $isSelesai = ($statusLayananClean === 'SELESAI');
    $isDitolak = ($statusLayananClean === 'DITOLAK');
    $belumSurvei = empty($kunjungan->survey);

    // LOGIKA LOCK
    $isLocked = !$isDitolak && $belumSurvei && !$isAdmin;
    
    $targetTimestamp = 0;
    if ($kunjungan->status_layanan == 'Diproses' && !empty($kunjungan->waktu_mulai_layanan) && isset($kunjungan->estimasi_sla)) {
        $waktuMulai = \Carbon\Carbon::parse($kunjungan->waktu_mulai_layanan);
        $estimasi = (int)$kunjungan->estimasi_sla;
        $satuan = strtolower(trim($kunjungan->satuan_sla ?? 'menit'));
        
        // SAMAKAN LOGIKA SLA DENGAN BACKEND (Jeda Saat Libur & Istirahat)
        $menitTersisa = ($satuan == 'hari') ? ($estimasi * 420) : $estimasi;
        $targetTime = $waktuMulai->copy();
        
        while ($menitTersisa > 0) {
            $targetTime->addMinute();
            $jam = $targetTime->format('H:i');
            $hari = $targetTime->dayOfWeekIso;
            
            $isJamKerja = (($jam >= '08:00' && $jam < '12:00') || ($jam >= '13:00' && $jam < '16:00'));
            $isHariKerja = ($hari >= 1 && $hari <= 5);
            
            if ($isHariKerja && $isJamKerja) {
                $menitTersisa--;
            }
        }
        
        $targetTimestamp = $targetTime->timestamp * 1000;
    }
@endphp
    
    <div class="flex flex-col w-full max-w-sm sm:max-w-md lg:max-w-lg my-4 space-y-5 sm:space-y-6">

        {{-- TOMBOL TOGGLE THEME --}}
        <div class="flex justify-end">
            <button id="theme-toggle" class="p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl sm:rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 hover:scale-105 active:scale-95 transition-all">
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-amber-500 animate-[spin_4s_linear_infinite]" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zm-.707-4.95a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm3.182-5.657a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
            </button>
        </div>

        {{-- 1. KARTU HERO UTAMA --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300 relative">
            <div class="{{ $kunjungan->status_layanan == 'Ditolak' ? 'gradient-red' : ($kunjungan->status_layanan == 'Antre' ? 'bg-gradient-to-br from-amber-500 to-orange-500' : 'gradient-bg') }} p-6 sm:p-8 text-white text-center relative overflow-hidden">
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

                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2 uppercase break-words px-2 leading-tight">
                    {{ $kunjungan->status_layanan }}
                </h1>
                <p class="text-white/80 text-xs font-medium mb-5 sm:mb-6 tracking-wide leading-relaxed px-1">
                    Harap simpan nomor antrean ini untuk memantau status terkini terkait urusan layanan yang sedang diproses.
                </p>

                <div class="relative bg-white/10 backdrop-blur-md py-3 sm:py-4 px-3 rounded-2xl sm:rounded-3xl border border-white/20 cursor-pointer transition-all hover:bg-white/15 active:scale-95 group w-full"
                     onclick="salinNomorAntrean('{{ $kunjungan->nomor_kunjungan }}')"
                     title="Klik untuk menyalin nomor antrean">
                    <p class="text-[2rem] sm:text-[3.5rem] font-black tracking-tighter leading-none break-all select-none w-full">
                        {{ $kunjungan->nomor_kunjungan }}
                    </p>
                    <span id="notif-salin" class="absolute left-1/2 -translate-x-1/2 -bottom-3 bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-md opacity-0 transition-all duration-300 pointer-events-none transform translate-y-1">
                        ✓ Tersalin
                    </span>
                </div>

                @if($kunjungan->status_layanan == 'Diproses' && $kunjungan->estimasi_sla)
                    <div class="mt-5 sm:mt-6 p-3 sm:p-4 bg-white/20 backdrop-blur-sm rounded-xl sm:rounded-2xl border border-white/30 animate-bounce-slow flex flex-col items-center">
                        <p class="text-[10px] sm:text-xs font-black uppercase tracking-widest text-white/90 mb-1">Estimasi Sisa Waktu</p>
                        <div class="flex items-center justify-center gap-2 flex-wrap">
                            <svg class="w-4 h-4 sm:w-5 h-5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p id="countdown-timer" data-target="{{ $targetTimestamp }}" class="text-lg sm:text-xl font-black text-white break-words text-center">
                                --:--:--
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- 2. BLOK KARTU DETAIL DATA & HASIL LAYANAN (DI-BLUR JIKA TERKUNCI) --}}
        {{-- Penambahan min-h-[420px] agar overlay lock memiliki ruang vertikal yang cukup --}}
        <div class="relative bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300 {{ $isLocked ? 'min-h-[420px] sm:min-h-[450px]' : '' }}">
            
            {{-- Wrapper Blur Otomatis --}}
            <div class="transition-all duration-500 h-full flex flex-col {{ $isLocked ? 'filter blur-[10px] grayscale opacity-20 pointer-events-none select-none h-full justify-center' : '' }}">
                
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
                    </div>
                    
                    @if($kunjungan->file_surat)
                    <div class="p-5 sm:p-6 bg-white dark:bg-slate-900 border-b border-slate-50 dark:border-slate-800 flex flex-col items-center text-center">
                        <div class="w-11 h-11 sm:w-12 sm:h-12 bg-emerald-100 dark:bg-emerald-900/30 rounded-xl sm:rounded-2xl flex items-center justify-center mb-3">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-tight">Dokumen Hasil Layanan</h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mb-5 px-2 leading-relaxed">Dokumen balasan Anda telah tersedia. Silakan klik tombol di bawah untuk mengunduh.</p>

                        <a href="{{ $kunjungan->file_surat }}" target="_blank" class="w-full flex items-center justify-center gap-2 py-3.5 px-4 bg-emerald-500 text-white text-xs sm:text-sm font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200 dark:shadow-none break-words text-center">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            <span>Unduh Berkas Hasil</span>
                        </a>
                    </div>
                    @endif
                @endif

                <div class="px-5 sm:px-8 py-4 sm:py-6 flex flex-wrap sm:flex-nowrap justify-between gap-4 border-b border-slate-50 dark:border-slate-800 bg-slate-50/30 dark:bg-slate-800/20">
                    <div class="min-w-0 flex-1 w-full sm:w-auto">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Pengunjung</p>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 break-words leading-tight">{{ $kunjungan->pengunjung->nama_lengkap }}</p>
                    </div>
                    <div class="sm:text-right flex-shrink-0 w-full sm:w-auto mt-2 sm:mt-0 border-t sm:border-t-0 border-slate-200 dark:border-slate-700 pt-3 sm:pt-0">
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1">Waktu Masuk</p>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300">{{ $kunjungan->created_at->format('H:i') }} WITA</p>
                    </div>
                </div>

                <div class="p-5 sm:p-6 border-b border-slate-100 dark:border-slate-800 bg-white dark:bg-slate-900 flex-1">
                    <div class="bg-slate-50 dark:bg-slate-800/40 rounded-xl sm:rounded-2xl p-4 border border-slate-100 dark:border-slate-800 h-full">
                        <p class="text-[10px] uppercase font-black tracking-widest text-[#0b3a82] dark:text-blue-400 mb-4">Detail Keperluan</p>
                        <div class="mb-4">
                            <p class="text-[9px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest mb-1">Jenis Layanan</p>
                            <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 italic leading-relaxed break-words">
                                {{ $kunjungan->keperluan_master->keterangan ?? '-' }}
                            </p>
                        </div>
                        @if(!empty($kunjungan->keperluan))
                        <div>
                            <p class="text-[9px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest mb-1">Keterangan Tambahan</p>
                            <p class="text-xs sm:text-sm font-medium text-slate-600 dark:text-slate-400 leading-relaxed italic break-words">
                                "{{ $kunjungan->keperluan }}"
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                @if($kunjungan->catatan_pimpinan)
                <div class="p-5 sm:p-6 bg-white dark:bg-slate-900">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-2 h-5 bg-[#0b3a82] dark:bg-blue-500 rounded-full"></div>
                        <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest">Pesan Pimpinan</h2>
                    </div>
                    <div class="p-4 sm:p-5 rounded-xl sm:rounded-2xl bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/50">
                        @if($kunjungan->status_pimpinan && $kunjungan->status_pimpinan != 'Menunggu')
                            <div class="flex items-center gap-3 mb-3 flex-wrap">
                                <span class="px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-tighter bg-[#0b3a82] dark:bg-blue-600 text-white">
                                    {{ $kunjungan->status_pimpinan }}
                                </span>
                            </div>
                        @endif
                        <p class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">Catatan Disposisi:</p>
                        <p class="text-xs sm:text-sm font-bold text-slate-700 dark:text-slate-300 italic break-words leading-relaxed">
                            "{{ $kunjungan->catatan_pimpinan }}"
                        </p>
                    </div>
                </div>
                @endif
            </div>

            {{-- PANEL OVERLAY GEMBOK (MUNCUL DI ATAS AREA BLUR) --}}
            {{-- Penambahan overflow-y-auto agar aman bila diakses dari device sangat kecil --}}
            @if($isLocked)
            <div class="absolute inset-0 z-30 flex flex-col items-center justify-center p-5 sm:p-6 text-center bg-slate-950/5 dark:bg-slate-950/40 backdrop-blur-sm overflow-y-auto">
                <div class="w-14 h-14 sm:w-16 sm:h-16 flex-shrink-0 {{ $isSelesai ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/50 dark:text-emerald-400 animate-bounce' : 'bg-slate-200 text-slate-500 dark:bg-slate-800 dark:text-slate-400' }} rounded-full flex items-center justify-center mb-4 shadow-xl border-4 border-white dark:border-slate-900 mt-4 sm:mt-0">
                    <i class="fa-solid fa-lock text-xl sm:text-2xl"></i>
                </div>
                
                @if($isSelesai)
                    <h3 class="text-lg sm:text-xl font-black text-slate-800 dark:text-white mb-2 leading-tight">Detail Hasil Terkunci</h3>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 dark:text-slate-300 mb-5 sm:mb-6 max-w-sm drop-shadow-sm leading-relaxed px-2">
                        Layanan Anda telah selesai. Mohon luangkan waktu <span class="font-bold">1 menit</span> untuk menilai layanan kami agar dapat membuka berkas dan hasil akhir.
                    </p>
                    <a href="{{ route('survey.form', $kunjungan->nomor_kunjungan) }}" class="px-6 sm:px-8 py-3.5 bg-gradient-to-r from-indigo-600 to-indigo-500 hover:from-indigo-700 hover:to-indigo-600 text-white text-xs sm:text-sm font-bold rounded-full shadow-xl shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all active:scale-95 flex items-center justify-center gap-2 group ring-4 ring-indigo-500/20 w-full sm:w-auto mb-4 sm:mb-0 text-center">
                        <i class="fa-solid fa-star text-amber-300 group-hover:rotate-180 transition-transform duration-500"></i> <span>Isi Survei Sekarang</span>
                    </a>
                @else
                    <h3 class="text-base sm:text-lg font-black text-rose-600 dark:text-rose-400 mb-2 leading-tight flex items-center justify-center flex-wrap gap-1">
                        <i class="fa-solid fa-triangle-exclamation animate-pulse"></i> <span>PERINGATAN PENTING!</span>
                    </h3>
                    <p class="text-[11px] sm:text-xs font-semibold text-slate-700 dark:text-slate-300 mb-5 max-w-sm drop-shadow-sm leading-relaxed px-2">
                        Nanti setelah antrean dinyatakan <span class="text-indigo-600 dark:text-blue-400 font-black">SELESAI</span> oleh petugas, Anda <span class="text-rose-600 dark:text-rose-400 font-black">WAJIB</span> mengisi survei kepuasan terlebih dahulu. Jika tidak, dokumen hasil dan detail pelayanan tidak bisa dibuka!
                    </p>
                    <div class="px-4 py-3 bg-slate-200 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-[11px] sm:text-xs font-bold rounded-2xl sm:rounded-full shadow-none flex items-center justify-center gap-2 w-full max-w-xs mb-4 sm:mb-0">
                        <i class="fa-solid fa-hourglass-half animate-spin flex-shrink-0"></i> <span class="break-words">Menunggu Pelayanan Selesai...</span>
                    </div>
                @endif
            </div>
            @endif
        </div>

        {{-- 3. KARTU RIWAYAT STATUS TIMELINE --}}
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300 w-full overflow-hidden">
            <div class="flex items-center gap-2 mb-6 sm:mb-8">
                <div class="w-2 h-5 bg-[#0b3a82] dark:bg-blue-500 rounded-full"></div>
                <h2 class="text-xs sm:text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-widest">Riwayat Status</h2>
            </div>

            @if($kunjungan->alasan_tolak)
            <div class="mt-4 mb-8 relative bg-white/80 dark:bg-slate-800/50 border border-rose-200 dark:border-rose-900/50 rounded-xl sm:rounded-2xl p-4 sm:p-5 shadow-sm overflow-hidden">
                <div class="absolute -top-6 -right-6 w-16 h-16 bg-rose-100 dark:bg-rose-900/20 rounded-full blur-xl"></div>
                <p class="text-[10px] font-black uppercase tracking-widest text-rose-500 mb-2">Alasan Penolakan</p>
                <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-400 font-medium leading-relaxed break-words">{{ $kunjungan->alasan_tolak }}</p>
            </div>
            @endif

            <div class="relative space-y-8 sm:space-y-10">
                <div class="timeline-line bg-slate-100 dark:bg-slate-800"></div>

                @php
                    $isSelesai = $kunjungan->status_layanan == 'Selesai';
                    $isDitolak = $kunjungan->status_layanan == 'Ditolak';
                    $waktuSelesaiAkhir = $kunjungan->waktu_selesai_layanan ?? $kunjungan->updated_at;
                @endphp
                
                {{-- Node Selesai / Tolak --}}
                <div class="relative pl-10 sm:pl-12">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 flex items-center justify-center 
                        {{ $isSelesai ? 'bg-emerald-500 shadow-lg shadow-emerald-100 dark:shadow-none' : ($isDitolak ? 'bg-rose-500 shadow-lg shadow-rose-100 dark:shadow-none' : 'bg-slate-200 dark:bg-slate-700') }}">
                        @if($isSelesai)
                            <svg class="w-3 h-3 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @elseif($isDitolak)
                            <svg class="w-3 h-3 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        @endif
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold break-words {{ $isSelesai ? 'text-slate-900 dark:text-slate-100' : ($isDitolak ? 'text-rose-600 dark:text-rose-400' : 'text-slate-300 dark:text-slate-600') }}">{{ $isDitolak ? 'Permohonan Ditolak' : 'Layanan Selesai' }}</h4>
                    <p class="text-[10px] sm:text-[11px] font-bold mt-1 break-words {{ $isDitolak ? 'text-rose-500 dark:text-rose-400' : ($isSelesai ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400 dark:text-slate-500') }}">
                        @if($isSelesai || $isDitolak) Pukul {{ \Carbon\Carbon::parse($waktuSelesaiAkhir)->format('H:i') }} WITA @else Menunggu penyelesaian @endif
                    </p>
                </div>

                @php
                    $isDiprosesAtauLebih = in_array($kunjungan->status_layanan, ['Diproses', 'Selesai', 'Ditolak']);
                @endphp
                
                {{-- Node Diproses --}}
                <div class="relative pl-10 sm:pl-12">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 flex items-center justify-center {{ $isDiprosesAtauLebih ? 'bg-[#0b3a82] dark:bg-blue-500 shadow-lg shadow-blue-100 dark:shadow-none' : 'bg-slate-200 dark:bg-slate-700' }}">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold break-words {{ $isDiprosesAtauLebih ? 'text-slate-900 dark:text-slate-100' : 'text-slate-300 dark:text-slate-600' }}">Sedang Diproses</h4>
                    <p class="text-[10px] sm:text-[11px] mt-1 break-words {{ $kunjungan->status_layanan == 'Diproses' ? 'font-bold text-[#0b3a82] dark:text-blue-400' : 'font-medium text-slate-400 dark:text-slate-500' }}">
                        @if($isDiprosesAtauLebih && !empty($kunjungan->waktu_mulai_layanan)) Pukul {{ \Carbon\Carbon::parse($kunjungan->waktu_mulai_layanan)->format('H:i') }} WITA @elseif($kunjungan->status_layanan == 'Diproses') Petugas sedang melayani Anda @else Menunggu giliran diproses @endif
                    </p>
                </div>

                {{-- Node Terdaftar --}}
                <div class="relative pl-10 sm:pl-12">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white dark:ring-slate-900 z-10 bg-amber-400 shadow-lg shadow-amber-100 dark:shadow-none flex items-center justify-center">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-xs sm:text-sm font-bold break-words text-slate-900 dark:text-slate-100">Tiket Terdaftar</h4>
                    <p class="text-[10px] sm:text-[11px] font-bold mt-1 break-words text-amber-600 dark:text-amber-400">Pukul {{ $kunjungan->created_at->format('H:i') }} WITA</p>
                </div>
            </div>
        </div>

        {{-- BUTTON AKSI BAWAH --}}
        @if(!$isLocked)
            <div class="pt-2 w-full">
                <a href="{{ url('/') }}" class="w-full flex items-center justify-center py-4 bg-[#0b3a82] dark:bg-slate-800 text-white font-extrabold rounded-2xl sm:rounded-3xl shadow-xl hover:bg-[#072a63] dark:hover:bg-slate-700 transition-all gap-3 text-xs sm:text-sm">
                    <i class="fa-solid fa-house"></i> <span>Kembali ke Beranda</span>
                </a>
            </div>
        @endif
    </div>

    <p class="text-center text-[10px] font-black text-slate-400 dark:text-slate-600 uppercase tracking-[0.4em] sm:tracking-[0.6em] pt-4 pb-6 w-full px-4 break-words text-center">Digital Gate System</p>

    {{-- AUTO REFRESH JIKA PROSES SEDANG BERJALAN --}}
    @if(!in_array($kunjungan->status_layanan, ['Selesai', 'Ditolak']))
        <script>setTimeout(function(){ window.location.reload(1); }, 15000);</script>
    @endif

    {{-- LIVE THEME SCRIPT --}}
    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');
        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                lightIcon.classList.remove('hidden'); darkIcon.classList.add('hidden');
            } else {
                darkIcon.classList.remove('hidden'); lightIcon.classList.add('hidden');
            }
        }
        updateIcons();
        if(themeToggleBtn) {
            themeToggleBtn.addEventListener('click', function() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark');
                }
                updateIcons();
            });
        }
    </script>

    {{-- COUNTDOWN SCRIPT --}}
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const timerElement = document.getElementById('countdown-timer');
        if (timerElement) {
            const targetTimestamp = parseInt(timerElement.getAttribute('data-target'));
            function updateCountdown() {
                const now = new Date().getTime();
                const distance = targetTimestamp - now;
                
                if (distance <= 0) {
                    timerElement.innerText = "Sedang Menyelesaikan...";
                    clearInterval(countdownInterval);
                    return;
                }
                
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                
                let displayString = "";
                
                if (days > 0) {
                    displayString += days + " Hari " + hours + " Jam " + minutes + " Mnt";
                } else if (hours > 0) {
                    displayString += hours + " Jam " + minutes + " Mnt " + seconds + " Dtk";
                } else if (minutes > 0) {
                    displayString += minutes + " Mnt " + seconds + " Dtk";
                } else {
                    displayString += seconds + " Dtk";
                }
                
                timerElement.innerText = displayString;
            }
            if (targetTimestamp > 0) { updateCountdown(); const countdownInterval = setInterval(updateCountdown, 1000); }
        }
    });

    function salinNomorAntrean(teks) {
        navigator.clipboard.writeText(teks).then(() => {
            const notif = document.getElementById('notif-salin');
            notif.classList.remove('opacity-0', 'translate-y-1'); notif.classList.add('opacity-100', 'translate-y-0');
            setTimeout(() => { notif.classList.remove('opacity-100', 'translate-y-0'); notif.classList.add('opacity-0', 'translate-y-1'); }, 2000);
        });
    }
    </script>
</body>
</html>