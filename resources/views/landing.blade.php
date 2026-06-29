<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Digital & Antrean</title>
    <script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .font-title {
        font-family: 'Outfit', sans-serif;
    }
</style>

    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .transition-all { transition: all 0.3s ease; }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-up {
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .swal2-backdrop-show {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
        }
    </style>
</head>
<body class="bg-[#F8FAFC] dark:bg-slate-950 min-h-screen flex flex-col text-slate-800 dark:text-slate-100 relative transition-colors duration-300" style="background-image: url('{{ asset('img/bg-poliban.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed;">

    <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/85 to-white/30 dark:from-slate-950/95 dark:via-slate-950/85 dark:to-slate-900/40 pointer-events-none z-0"></div>

    <header class="w-full px-6 lg:px-12 py-6 flex justify-between items-center max-w-[90rem] mx-auto relative z-10">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 flex items-center justify-center bg-white rounded-full p-1 shadow-sm border border-slate-100 dark:border-slate-800">
                <img src="{{ asset('img/logo-poliban.png') }}" alt="Logo Poliban" class="w-full h-full object-contain">
            </div>
            <div>
                <h2 class="font-extrabold text-slate-900 dark:text-white text-lg lg:text-xl leading-tight tracking-tight">Politeknik Negeri Banjarmasin</h2>
                <p class="text-xs lg:text-sm text-amber-500 font-bold uppercase tracking-wide">Jurusan Teknik Elektro</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            {{-- BUTTON TOGGLE DARK MODE --}}
            <button id="theme-toggle" class="p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-full shadow-sm border border-slate-200 dark:border-slate-800 hover:scale-105 active:scale-95 transition-all">
                {{-- Ikon Bulan --}}
                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                </svg>
                {{-- Ikon Matahari --}}
                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-amber-500 animate-[spin_4s_linear_infinite]" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zm-.707-4.95a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm3.182-5.657a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" fill-rule="evenodd" clip-rule="evenodd"></path>
                </svg>
            </button>

            <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-6 py-2.5 bg-[#002B5B] border border-transparent rounded-full text-sm font-bold text-white hover:bg-blue-950 transition-all shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
                Login Admin
            </a>

            <a href="{{ route('login') }}" class="sm:hidden inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-full text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-blue-600 transition-all shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                </svg>
            </a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 lg:p-8 w-full relative z-10">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <div class="flex flex-col justify-center order-1 lg:order-1 px-4 lg:px-0">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 dark:bg-slate-900/80 shadow-sm border border-slate-200 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 w-max mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sistem Online Beroperasi
                </div>

<h1 class="font-title text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-black leading-[1.1] mb-6 text-slate-900 dark:text-white tracking-tight">
    SIPANDU-ELEKTRO <br class="hidden lg:inline">
    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-900 via-blue-600 to-sky-500 dark:from-blue-400 dark:via-blue-300 dark:to-teal-400 block mt-2 font-extrabold text-2xl md:text-3xl lg:text-4xl xl:text-5xl uppercase tracking-normal">
        Jurusan Teknik Elektro
    </span>
</h1>

                <p class="text-slate-600 dark:text-slate-400 mb-10 text-base lg:text-lg leading-relaxed max-w-md">
                    Dapatkan nomor antrean Anda sekarang dan pantau estimasi waktu (SLA) secara real-time.
                </p>

                <div class="bg-white dark:bg-slate-900 p-2 rounded-2xl md:rounded-full shadow-lg border border-slate-100 dark:border-slate-800 flex flex-col md:flex-row items-center max-w-md gap-2 relative z-10">
                    <div class="w-full flex items-center pl-4 pr-2">
                        <div class="text-slate-400 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input type="text" id="inputLacak" placeholder="CEK NOMOR ANTREAN (MIS: IN-123)" class="w-full py-3 bg-transparent text-slate-700 dark:text-slate-200 font-bold outline-none uppercase placeholder:font-normal placeholder:text-slate-400 dark:placeholder:text-slate-500 text-sm">
                    </div>
                    <button type="button" onclick="lacakAntrean()" class="w-full md:w-auto bg-[#002B5B] text-white font-bold px-8 py-3 rounded-xl md:rounded-full hover:bg-blue-950 transition-all active:scale-95 whitespace-nowrap shadow-md">
                        Lacak
                    </button>
                </div>

<div class="mt-12 h-24 w-full max-w-md rounded-2xl bg-white/60 dark:bg-slate-900/60 backdrop-blur-sm border border-white dark:border-slate-800 shadow-sm flex flex-col items-center justify-center overflow-hidden relative p-4">

    <div class="text-[10px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500 mb-1 z-10">
        Status Loket Melayani
    </div>

    <div class="w-full h-12 flex items-center justify-center overflow-hidden relative" style="perspective: 1000px;">
        <div id="antrean-container" class="transition-all duration-700 ease-in-out transform flex flex-col items-center justify-center w-full" style="transform-origin: center center -20px; transform: rotateX(0deg); opacity: 1;">
            <span class="text-slate-400 text-xs italic animate-pulse">Menghubungkan ke sistem...</span>
        </div>
    </div>

    <div class="absolute bottom-2 right-3 flex items-center gap-1.5">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="text-[9px] font-medium text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Live</span>
    </div>
</div>
            </div>

            <div class="order-2 lg:order-2 bg-white dark:bg-slate-900 p-6 lg:p-10 rounded-[2rem] shadow-2xl shadow-slate-200/50 dark:shadow-none border border-slate-100 dark:border-slate-800">
                <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-8">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-1.5 h-6 bg-amber-500 rounded-full"></div>
                            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                                Buku Tamu Digital
                            </h2>
                        </div>
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed max-w-xl">
                            Isi form di bawah ini untuk memulai layanan kunjungan Anda.
                            Jika sebelumnya pernah berkunjung, Anda cukup memasukkan
                            <span class="font-bold text-blue-700 dark:text-blue-400">NIM/NIP/NIK</span>
                            untuk mengambil data otomatis.
                        </p>
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-2 w-full md:w-auto">
                        <button type="button" onclick="cekPengunjungLama()" class="w-full md:w-auto px-5 py-2.5 border-2 border-slate-800 dark:border-slate-600 text-slate-800 dark:text-slate-300 font-bold text-xs rounded-full hover:bg-slate-50 dark:hover:bg-slate-800 transition-all whitespace-nowrap">
                            Cek Data Sebelumnya
                        </button>
                        <span id="status-cek" class="text-xs font-bold text-amber-500"></span>
                    </div>

                    <div id="loading-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
                        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-11/12 text-center animate-fade-in">
                            <div class="w-12 h-12 border-4 border-blue-800 border-t-transparent rounded-full animate-spin mb-4"></div>
                            <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Memproses Data</h3>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">Mohon tunggu sebentar, sistem sedang mengecek data Anda...</p>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                <div class="mb-8 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 p-4 rounded-xl font-medium border border-emerald-100 dark:border-emerald-900/50 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-8 bg-red-50 dark:bg-red-950/50 text-red-700 dark:text-red-400 p-4 rounded-xl text-sm border border-red-100 dark:border-red-900/50">
                    <div class="font-bold flex items-center gap-2 mb-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Gagal menyimpan data:
                    </div>
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form id="formKunjungan" action="{{ route('kunjungan.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/xl" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" id="nama_lengkap" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-400" placeholder="Jhon Doe">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">NIM/NIP/NIK <span class="text-slate-400 dark:text-slate-500 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" id="identitas_no" name="identitas_no" value="{{ old('identitas_no') }}" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-400" placeholder="C03...">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">No. WhatsApp</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg>
                                </div>
                                <input type="text" id="no_telepon" name="no_telepon" value="{{ old('no_telepon') }}" required class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-400" placeholder="0812...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Asal Instansi / Kategori</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" id="asal_instansi" name="asal_instansi" value="{{ old('asal_instansi') }}" required class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 placeholder:text-slate-400" placeholder="Mis: Poliban">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Tujuan (Prodi/Bagian)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                </div>
                                <select id="prodi_id" name="prodi_id" required class="w-full pl-11 pr-10 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 appearance-none cursor-pointer">
                                    <option value="" disabled selected>Pilih Program Studi...</option>
                                    @foreach($prodi as $p)
                                        <option value="{{ $p->id }}" {{ old('prodi_id') == $p->id ? 'selected' : '' }}>{{ $p->nama }}</option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Kategori Keperluan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                        <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
                                    </svg>
                                </div>
                                <select id="keperluan_id" name="keperluan_id" required class="w-full pl-11 pr-10 py-3 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 appearance-none cursor-pointer">
                                    <option value="" disabled selected>Pilih Keperluan...</option>
                                    @foreach($keperluan as $k)
                                        <option value="{{ $k->id }}" {{ old('keperluan_id') == $k->id ? 'selected' : '' }}>
                                            {{ $k->keterangan }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none text-slate-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Keperluan (Detail)</label>
                        <textarea id="catatan_keperluan" name="catatan_keperluan" rows="3" class="w-full p-4 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:bg-white transition-all outline-none text-slate-700 dark:text-slate-200 resize-none placeholder:text-slate-400" placeholder="Ceritakan singkat tujuan kedatangan Anda...">{{ old('catatan_keperluan') }}</textarea>
                    </div>

                    <button type="submit" id="btnSubmit" class="w-full py-4 mt-4 bg-[#002B5B] text-white font-bold text-lg rounded-full hover:bg-blue-950 transition-all hover:-translate-y-0.5 shadow-md disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                        Daftar Kunjungan
                    </button>
                </form>
            </div>
        </div>
    </main>

    <section class="w-full px-6 lg:px-12 py-16 max-w-[90rem] mx-auto text-center relative z-10">
        <h2 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-4">Pelayanan Cerdas Terintegrasi</h2>
        <p class="text-slate-500 dark:text-slate-400 mb-12 max-w-2xl mx-auto text-sm md:text-base">
            Sistem kami dirancang khusus untuk memberikan pengalaman layanan yang transparan, terukur, dan responsif.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
            <div id="feature-card-0" class="feature-card bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800/60 hover:shadow-xl transition-all duration-500">
                <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-950/40 flex items-center justify-center text-purple-500 dark:text-purple-400 mb-6 icon-box transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 text-lg">Pelacakan Real-Time & SLA</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Cek nomor antrean untuk memantau status layanan dan estimasi waktu selesai tanpa perlu login.</p>
            </div>

            <div id="feature-card-1" class="feature-card bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800/60 hover:shadow-xl transition-all duration-500">
                <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-950/40 flex items-center justify-center text-purple-500 dark:text-purple-400 mb-6 icon-box transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 text-lg">Notifikasi</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Integrasi WhatsApp/Email otomatis untuk mempercepat persetujuan layanan oleh pimpinan.</p>
            </div>

            <div id="feature-card-2" class="feature-card bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800/60 hover:shadow-xl transition-all duration-500">
                <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-950/40 flex items-center justify-center text-purple-500 dark:text-purple-400 mb-6 icon-box transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 text-lg">Survei Kepuasan Anonim</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Sistem penilaian pelayanan terintegrasi dengan identitas yang disamarkan untuk menjaga privasi.</p>
            </div>

            <div id="feature-card-3" class="feature-card bg-white dark:bg-slate-900 p-8 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-800/60 hover:shadow-xl transition-all duration-500">
                <div class="w-12 h-12 rounded-full bg-purple-50 dark:bg-purple-950/40 flex items-center justify-center text-purple-500 dark:text-purple-400 mb-6 icon-box transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white mb-3 text-lg">Aman & Responsif</h3>
                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed">Akses mudah dari semua perangkat (tanpa instal aplikasi) dengan keamanan sesi Auto-Logout.</p>
            </div>
        </div>
    </section>

    <footer class="w-full px-6 lg:px-12 py-8 mt-auto text-slate-500 dark:text-slate-400 border-t border-slate-200 dark:border-slate-800/80 relative z-10 bg-white/40 dark:bg-slate-950/80 backdrop-blur-sm">
        <div class="max-w-[90rem] mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium">
            <div class="text-center md:text-left">
                <p class="font-bold text-slate-800 dark:text-slate-200 mb-1">Sistem Informasi Pelayanan Publik & Monitoring KPI</p>
                <p>&copy; 2026 Jurusan Teknik Elektro - Politeknik Negeri Banjarmasin</p>
            </div>
            <div class="flex gap-6 items-center">
                <a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Privasi & Keamanan</a>
                <a href="#" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Panduan Layanan</a>
            </div>
        </div>
    </footer>

<script>
        function lacakAntrean() {
            const inputField = document.getElementById('inputLacak');
            const nomorKunjungan = inputField.value.trim().toUpperCase();

            if (nomorKunjungan === "") {
                alert("Silakan masukkan Nomor Antrean!");
                return;
            }

            const urlTujuan = "{{ url('/status') }}/" + nomorKunjungan;
            window.location.href = urlTujuan;
        }

        document.getElementById('inputLacak').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                lacakAntrean();
            }
        });
    </script>

    <script>
        async function cekPengunjungLama(){
            const identitas = document.getElementById('identitas_no').value;
            const status = document.getElementById('status-cek');
            const loadingModal = document.getElementById('loading-modal');

            if(!identitas){
                status.innerHTML = '<span class="text-rose-500">Isi NIM/NIP/NIK dulu</span>';
                return;
            }

            status.innerHTML = '<span class="text-indigo-500 dark:text-indigo-400">Mencari data...</span>';

            if (loadingModal) loadingModal.classList.remove('hidden');

            try {
                const response = await fetch(`/cek-pengunjung/${identitas}`);
                const data = await response.json();

                if(data.status === 'found'){
                    document.getElementById('nama_lengkap').value = data.data.nama_lengkap ?? '';
                    document.getElementById('no_telepon').value = data.data.no_telepon ?? '';
                    document.getElementById('asal_instansi').value = data.data.asal_instansi ?? '';
                    status.innerHTML = '<span class="text-emerald-600 dark:text-emerald-400">Data ditemukan ✔</span>';
                } else {
                    status.innerHTML = '<span class="text-amber-500">Data tidak ditemukan</span>';
                }
            } catch(err) {
                status.innerHTML = '<span class="text-rose-500">Gagal cek data</span>';
            } finally {
                if (loadingModal) loadingModal.classList.add('hidden');
            }
        }

        document.getElementById('formKunjungan').addEventListener('submit', function (e) {
            const form = this;
            const btnSubmit = document.getElementById('btnSubmit');

            if (form.getAttribute('data-submitting') === 'true') {
                e.preventDefault();
                return false;
            }

            form.setAttribute('data-submitting', 'true');
            btnSubmit.disabled = true;

            btnSubmit.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Memproses Pendaftaran...
            `;
        });
    </script>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');

        function updateToggleIcons() {
            if (document.documentElement.classList.contains('dark')) {
                lightIcon.classList.remove('hidden');
                darkIcon.classList.add('hidden');
            } else {
                darkIcon.classList.remove('hidden');
                lightIcon.classList.add('hidden');
            }
        }

        updateToggleIcons();

        themeToggleBtn.addEventListener('click', function() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            updateToggleIcons();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let currentFeatureIndex = 0;
            const totalCards = 4;

            function rotateHighlight() {
                for (let i = 0; i < totalCards; i++) {
                    const card = document.getElementById(`feature-card-${i}`);
                    if(!card) continue;
                    const iconBox = card.querySelector('.icon-box');

                    card.classList.remove('shadow-xl', 'scale-105', 'border-purple-200', 'dark:border-purple-900', 'bg-gradient-to-b', 'from-white', 'to-purple-50/30', 'dark:from-slate-900', 'dark:to-purple-950/20');
                    card.classList.add('shadow-sm', 'border-slate-100', 'dark:border-slate-800/60');

                    if(iconBox) {
                        iconBox.classList.remove('bg-purple-500', 'text-white', 'dark:bg-purple-600');
                        iconBox.classList.add('bg-purple-50', 'text-purple-500', 'dark:bg-purple-950/40', 'dark:text-purple-400');
                    }
                }

                const activeCard = document.getElementById(`feature-card-${currentFeatureIndex}`);
                if(activeCard) {
                    const activeIcon = activeCard.querySelector('.icon-box');
                    activeCard.classList.remove('shadow-sm', 'border-slate-100', 'dark:border-slate-800/60');
                    activeCard.classList.add('shadow-xl', 'scale-105', 'border-purple-200', 'dark:border-purple-900', 'bg-gradient-to-b', 'from-white', 'to-purple-50/30', 'dark:from-slate-900', 'dark:to-purple-950/20');

                    if (activeIcon) {
                        activeIcon.classList.remove('bg-purple-50', 'text-purple-500', 'dark:bg-purple-950/40', 'dark:text-purple-400');
                        activeIcon.classList.add('bg-purple-500', 'text-white', 'dark:bg-purple-600');
                    }
                }

                currentFeatureIndex = (currentFeatureIndex + 1) % totalCards;
            }

            rotateHighlight();
            setInterval(rotateHighlight, 3000);
        });
    </script>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById('antrean-container');
    let antreanList = [];
    let currentIndex = 0;
    let animationInterval = null;
    let isFirstLoad = true;

    function updateDisplay() {
        // Jika tidak ada data sama sekali
        if (antreanList.length === 0) {
            clearInterval(animationInterval);
            animationInterval = null;
            container.innerHTML = `<span class="text-slate-400 dark:text-slate-500 font-medium text-sm">Tidak Ada Antrean Diproses</span>`;
            return;
        }

        // Jika hanya ada 1 data, tampilkan warna Biru (Statis)
        if (antreanList.length === 1) {
            clearInterval(animationInterval);
            animationInterval = null;
            container.innerHTML = `
                <span class="text-xl font-black text-blue-600 dark:text-blue-400 tracking-wider bg-blue-50 dark:bg-blue-950/40 px-4 py-1 rounded-xl border border-blue-100 dark:border-blue-900/50 min-w-[140px] text-center shadow-inner animate-fade-in">
                    ${antreanList[0].nomor}
                </span>
            `;
        } else {
            // Jika data lebih dari 1, jalankan animasi perputaran (Warna Hijau)
            if (!animationInterval) {
                startRotation();
            }
        }
    }

function startRotation() {
        clearInterval(animationInterval);

        if (antreanList[currentIndex]) {
            renderTickerItem(antreanList[currentIndex].nomor);
        }

        animationInterval = setInterval(() => {
            // 1. Efek Roda Berputar ke Atas/Belakang (Menghilang)
            container.style.transition = "all 350ms ease-in-out"; // Durasi rotasi keluar cepat
            container.style.transform = "rotateX(90deg)";
            container.style.opacity = "0";

            setTimeout(() => {
                // 2. Ganti nomor antrean berikutnya saat posisi tidak terlihat
                currentIndex = (currentIndex + 1) % antreanList.length;
                renderTickerItem(antreanList[currentIndex].nomor);

                // 3. Pindahkan sudut roda bersiap masuk menggelinding dari bawah/depan (-90 derajat)
                container.style.transition = "none";
                container.style.transform = "rotateX(-90deg)";

                // Trigger reflow browser agar perubahan instan tanpa animasi di atas terbaca
                container.offsetHeight;

                // 4. Efek Masuk: Gelindingkan roda kembali tegak lurus ke tengah (0 derajat)
                container.style.transition = "all 450ms ease-out"; // Durasi menggelinding masuk lembut
                container.style.transform = "rotateX(0deg)";
                container.style.opacity = "1";
            }, 350); // Jeda waktu tunggu saat roda berputar hilang

        }, 3500); // Berganti nomor setiap 3.5 detik
    }

    function renderTickerItem(nomorAntrean) {
        container.innerHTML = `
            <span class="text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-wider bg-emerald-50 dark:bg-emerald-950/40 px-4 py-1 rounded-xl border border-emerald-100 dark:border-emerald-900/50 min-w-[140px] text-center shadow-inner">
                ${nomorAntrean}
            </span>
        `;
    }

    function fetchAntreanRealtime() {
        fetch('/antrean-diproses')
            .then(response => {
                if (!response.ok) throw new Error('API Bermasalah');
                return response.json();
            })
            .then(res => {
                if (res.status === 'success') {
                    const newData = res.data;

                    // Bandingkan isi data secara presisi menggunakan JSON stringify
                    if (JSON.stringify(newData) !== JSON.stringify(antreanList) || isFirstLoad) {
                        antreanList = newData;

                        // Jika data berubah atau muatan pertama, set index kembali ke awal
                        if (isFirstLoad || currentIndex >= antreanList.length) {
                            currentIndex = 0;
                        }

                        isFirstLoad = false;
                        updateDisplay();
                    }
                }
            })
            .catch(err => {
                console.error("Gagal memperbarui antrean loket:", err);
            });
    }

    // Ambil data pertama kali saat web dibuka
    fetchAntreanRealtime();

    // Laaaakukan sinkronisasi background polling ke server setiap 5 detik
    setInterval(fetchAntreanRealtime, 5000);
});
    </script>
</body>
</html>
