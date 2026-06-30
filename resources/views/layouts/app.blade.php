<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - @yield('title')</title>

<script src="https://cdn.tailwindcss.com"></script>
    <script>
        // 2. Satukan konfigurasi Dark Mode dan Animasi Marquee di sini agar tidak saling menimpa
tailwind.config = {
    darkMode: 'class',
    theme: {
        extend: {
            animation: {
                // Diubah dari 25s ke 45s agar jalannya lebih lambat dan tenang
                'marquee': 'marquee 45s linear infinite',
            },
            keyframes: {
                marquee: {
                    '0%': { transform: 'translateX(0)' },
                    '100%': { transform: 'translateX(-100%)' },
                }
            }
        }
    }
}
    </script>

    <script>
        // 3. Skrip Cek Otomatis Dark Mode tetap di sini
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        *{
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body{
            background: #f6f7fb;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Styling khusus saat Mode Gelap Aktif */
        .dark body {
            background: #0f172a; /* bg-slate-900 */
            color: #f8fafc;
        }

        ::-webkit-scrollbar{
            width:6px;
        }

        ::-webkit-scrollbar-thumb{
            background:#dbe1ea;
            border-radius:20px;
        }

        .dark ::-webkit-scrollbar-thumb {
            background: #334155;
        }

        .sidebar-scroll::-webkit-scrollbar{
            display:none;
        }

        .sidebar-scroll{
            -ms-overflow-style:none;
            scrollbar-width:none;
        }

        .menu-active{
            background: linear-gradient(90deg,#f3e8ff 0%, #ede9fe 100%);
            color:#7c3aed;
            font-weight:800;
            position:relative;
        }

        .dark .menu-active {
            background: linear-gradient(90deg, #3b0764 0%, #1e1b4b 100%);
            color: #c084fc;
        }

        .menu-active::before{
            content:'';
            position:absolute;
            left:0;
            top:14px;
            bottom:14px;
            width:4px;
            border-radius:999px;
            background:#9333ea;
        }

        .menu-hover:hover{
            background:#f8fafc;
            color:#0f172a;
        }

        .dark .menu-hover:hover {
            background: #1e293b;
            color: #f8fafc;
        }

        .glass{
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(14px);
        }

        .dark .glass {
            background: rgba(15, 23, 42, 0.8);
        }
        .swal2-backdrop-show {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
        }
        /* =========================================================================
   ANIMASI RUNNING TEXT (MARQUEE) UNTUK NAVBAR
   ========================================================================= */
@keyframes marquee {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

.animate-marquee {
    animation: marquee 25s linear infinite;
}

/* Jeda animasi saat kursor diarahkan (di-hover) ke area pengumuman */
.group-hover\:pause:group-hover {
    animation-play-state: paused;
}
    </style>
</head>

<body class="h-screen overflow-hidden text-slate-800 dark:text-slate-100">

{{-- AMBIL DATA USER DARI SESSION MANUAL --}}
@php
    $userSession = (object) session('user');
@endphp

<div class="flex h-screen overflow-hidden">

    {{-- MOBILE OVERLAY --}}
    <div id="sidebarOverlay"
        onclick="toggleSidebar()"
        class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden">
    </div>

    {{-- SIDEBAR --}}
    <aside id="sidebar"
        class="fixed lg:relative z-50 lg:z-0 top-0 left-0 h-screen w-[290px] bg-white dark:bg-slate-800 border-r border-slate-100 dark:border-slate-700 flex flex-col transition-all duration-300 -translate-x-full lg:translate-x-0">

        {{-- LOGO --}}
        <div class="h-24 px-6 flex items-center border-b border-slate-100 dark:border-slate-700">

            <div class="w-12 h-12 rounded-2xl overflow-hidden flex items-center justify-center bg-indigo-50 dark:bg-slate-700 shadow-sm flex-shrink-0">
                <img src="{{ asset('img/logo-poliban.png') }}"
                    alt="Logo"
                    class="w-9 h-9 object-contain">
            </div>

            <div class="ml-4 overflow-hidden">
                <h1 class="font-black text-slate-900 dark:text-white text-sm leading-tight truncate">
                    Jurusan Teknik Elektro
                </h1>

                <p class="text-[10px] uppercase tracking-[0.25em] text-slate-400 dark:text-slate-500 font-bold mt-1">
                    Admin Panel
                </p>
            </div>

        </div>

        {{-- MENU --}}
        <div class="flex-1 overflow-y-auto sidebar-scroll px-4 py-6">
<nav class="space-y-2">

                {{-- DASHBOARD --}}
                @if(in_array($userSession->role_id, [1,2]))
                <a href="{{ route('dashboard') }}"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200
                    {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

                    <i class="fa-solid fa-chart-pie text-lg"></i>

                    <span class="font-bold text-sm {{ request()->routeIs('dashboard') ? 'text-white' : '' }}">
                        Dashboard
                    </span>

                </a>
                @endif

                {{-- ANTREAN --}}
                @if(in_array($userSession->role_id, [1,2]))
                <a href="{{ route('dashboard.antrean') }}"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200
                    {{ request()->routeIs('dashboard.antrean') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

                    <i class="fa-solid fa-users-viewfinder text-lg"></i>

                    <span class="font-bold text-sm {{ request()->routeIs('dashboard.antrean') ? 'text-white' : '' }}">
                        Manajemen Antrean
                    </span>

                </a>
                @endif

                {{-- ANALYTICS KPI (HANYA BAGIAN INI YANG DIUBAH) --}}
<a href="{{ route('dashboard.analytics') }}"
    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200 group
    {{ (request()->routeIs('dashboard.analytics') || (request()->routeIs('dashboard') && !in_array($userSession->role_id, [1,2])))
        ? 'bg-blue-600 text-white shadow-lg shadow-blue-500/20 font-black'
        : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

    <i class="fa-solid fa-chart-simple text-lg transition-colors
        {{ (request()->routeIs('dashboard.analytics') || (request()->routeIs('dashboard') && !in_array($userSession->role_id, [1,2])))
            ? 'text-white'
            : 'text-slate-400 group-hover:text-blue-600 dark:text-slate-500' }}"></i>

    <span class="font-bold text-sm">
        Analytics KPI
    </span>

</a>

                {{-- LAPORAN --}}
                <a href="{{ route('dashboard.laporan') }}"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200
                    {{ request()->routeIs('dashboard.laporan') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

                    <i class="fa-solid fa-file-export text-lg"></i>

                    <span class="font-bold text-sm {{ request()->routeIs('dashboard.laporan') ? 'text-white' : '' }}">
                        Laporan Ekspor
                    </span>

                </a>

                {{-- ULASAN --}}
                <a href="{{ route('dashboard.ulasan') }}"
                    class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200
                    {{ request()->routeIs('dashboard.ulasan') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

                    <i class="fa-solid fa-comment-dots text-lg"></i>

                    <span class="font-bold text-sm {{ request()->routeIs('dashboard.ulasan') ? 'text-white' : '' }}">
                        Ulasan Pengunjung
                    </span>

                </a>

                {{-- PIMPINAN --}}
                @if($userSession->role_id != 1 && $userSession->role_id != 2)

                <div class="pt-5 mt-5 border-t-2 border-red-500/30">

                    <p class="px-4 mb-3 text-[10px] uppercase tracking-[0.3em] text-red-600 dark:text-red-400 font-black">
                        Tugas Pimpinan
                    </p>

                    <a href="{{ route('pimpinan.konfirmasi') }}"
                        class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200 relative
                        {{ request()->routeIs('pimpinan.konfirmasi') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20 font-black' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600' }}">

                    <i class="fa-solid fa-file-signature text-lg"></i>

                    <span class="font-bold text-sm {{ request()->routeIs('pimpinan.konfirmasi') ? 'text-white' : '' }}">
                        Konfirmasi Masuk
                    </span>

                    <span class="absolute right-5 w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>

                    </a>

                </div>

                @endif

                {{-- SUPER ADMIN --}}
                @if($userSession->role_id == 1)

                <div class="pt-5 mt-5 border-t-2 border-red-500/30">

                    <p class="px-4 mb-3 text-[10px] uppercase tracking-[0.3em] text-red-600 dark:text-red-400 font-black">
                        System Admin
                    </p>

                    <a href="{{ route('dashboard.control_panel') }}"
                        class="flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-200
                        {{ request()->routeIs('dashboard.control_panel') ? 'bg-blue-600 text-amber-400 shadow-md shadow-blue-500/20' : 'text-slate-400 hover:bg-slate-100 hover:text-blue-600 dark:text-slate-500 dark:hover:bg-slate-800' }}">

                        <i class="fa-solid fa-gears text-lg"></i>

                        <span class="font-bold text-sm {{ request()->routeIs('dashboard.control_panel') ? 'text-white' : '' }}">
                            Sistem Control
                        </span>

                    </a>

                </div>

                @endif

            </nav>

        </div>

        {{-- LOGOUT --}}
        <div class="p-5 border-t border-slate-100 dark:border-slate-700">

            <form action="{{ route('logout') }}"
                method="POST"
                id="logout-form">

                @csrf

                <button type="button"
                    onclick="confirmLogout()"
                    class="w-full flex items-center gap-3 px-5 py-4 rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all">

                    <i class="fa-solid fa-arrow-right-from-bracket"></i>

                    <span class="font-bold text-sm">
                        Keluar
                    </span>

                </button>

            </form>

        </div>

    </aside>

    {{-- MAIN --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- TOPBAR --}}
        <header class="h-24 px-4 sm:px-6 lg:px-8 border-b border-slate-100 dark:border-slate-700 glass sticky top-0 z-30 flex-shrink-0">

            <div class="h-full flex items-center justify-between">

{{-- LEFT --}}
<div class="flex items-center gap-4 flex-1">

    {{-- MOBILE BUTTON --}}
    <button onclick="toggleSidebar()" class="lg:hidden w-11 h-11 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300 shadow-sm">
        <i class="fa-solid fa-bars"></i>
    </button>

    {{-- TIMER AUTO REFRESH --}}
    <div class="flex items-center gap-3 px-3 sm:px-4 py-2 rounded-full bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 shrink-0">
        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse flex-shrink-0"></span>
        <span id="refresh-timer-text" class="text-[9px] sm:text-[11px] uppercase tracking-widest text-emerald-600 dark:text-emerald-400 font-black">
            Auto-Refresh: 30s
        </span>
    </div>

    {{-- RUNNING TEXT BROADCAST PEMBERITAHUAN (DILEBARKAN) --}}
<div class="hidden md:flex items-center flex-1 max-w-3xl mx-2 bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/40 rounded-full py-1.5 px-4 overflow-hidden shadow-inner group relative">
    <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 font-bold text-xs shrink-0 bg-amber-50 dark:bg-slate-900 pr-2 z-10">
        <i class="fa-solid fa-bullhorn animate-bounce"></i>
        <span>PENGUMUMAN:</span>
    </div>
    <div class="w-full overflow-hidden relative flex items-center h-4">
        <div class="absolute whitespace-nowrap text-xs font-semibold text-slate-600 dark:text-slate-300 animate-marquee">
            📢 Demi keamanan data, pastikan Anda <span class="text-amber-600 dark:text-amber-400 font-black">TIDAK mengunduh laporan atau mengekspor data</span> saat hitung mundur Auto-Refresh berada di detik-detik terakhir! Pasang kursor pada input untuk menjeda sementara.
        </div>
    </div>
</div>

</div>

                {{-- RIGHT --}}
                <div class="flex items-center gap-2 sm:gap-4">

                    {{-- BUTTON TOGGLE DARK MODE --}}
                    <button id="theme-toggle" class="p-2.5 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 rounded-xl sm:rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800 hover:scale-105 active:scale-95 transition-all">
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5 text-slate-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5 text-amber-500 animate-[spin_4s_linear_infinite]" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 14.05a1 1 0 011.414 0l.707.707a1 1 0 01-1.414 1.414l-.707-.707a1 1 0 010-1.414zm-.707-4.95a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm3.182-5.657a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

{{-- NOTIF --}}
@if($userSession->role_id == 2)
{{-- REVISI: Tambahkan z-[100] pada pembungkus utama --}}
<div class="relative inline-block text-left z-[100]">
    <button type="button" onclick="toggleNotifDropdown()" id="btnNotifTrigger"
        class="relative w-11 h-11 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all">
        <i class="fa-regular fa-bell text-lg"></i>
        <span id="notif-dot" class="absolute top-2 right-2 w-2 h-2 bg-amber-500 rounded-full hidden animate-ping"></span>
        <span id="notif-count" class="absolute -top-1 -right-1 text-[9px] bg-rose-600 text-white px-1.5 py-0.5 rounded-full font-bold hidden">0</span>
    </button>

    {{-- REVISI: Ubah z-[99] menjadi z-[101] --}}
    <div id="notifDropdown"
        class="hidden absolute right-0 mt-2 w-80 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-100 dark:border-slate-800 z-[101] overflow-hidden transform scale-95 opacity-0 transition-all duration-200 origin-top-right">

        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-between items-center">
            <span class="text-xs font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Pemberitahuan Tamu</span>
            <span class="text-[10px] bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 font-bold px-2 py-0.5 rounded-full">Baru</span>
        </div>

        <div class="max-h-64 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800" id="notifContainer"></div>

        <a id="notifFooterLink" href="{{ route('dashboard.antrean') }}" class="hidden block text-center py-3 bg-slate-50 dark:bg-slate-900/50 hover:bg-slate-100 dark:hover:bg-slate-800 text-xs font-bold text-blue-600 dark:text-blue-400 border-t border-slate-100 dark:border-slate-800 transition-colors">
            Lihat Semua Antrean
        </a>
    </div>
</div>
@endif

                    {{-- USER --}}
                    <div class="flex items-center gap-3">
                        <div class="hidden md:block text-right">
                            <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight">
                                {{ $userSession->name }}
                            </h3>
                            <p class="text-[10px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold mt-1">
                                @if($userSession->role_id == 1)
                                    Master Administrator
                                @elseif($userSession->email === 'kajur.elektro@poliban.ac.id')
                                    Ketua Jurusan
                                @elseif($userSession->role_id == 2)
                                    Admin Prodi
                                @else
                                    Ketua Program Studi
                                @endif
                            </p>
                        </div>
                        <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-full bg-indigo-500 text-white flex items-center justify-center font-black shadow-lg text-sm sm:text-base flex-shrink-0">
                            {{ strtoupper(substr($userSession->name, 0, 2)) }}
                        </div>
                    </div>

                </div>

            </div>

        </header>

        {{-- CONTENT CONTAINER --}}
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 min-w-0">
            @yield('content')
        </div>

    </main>

</div>

{{-- MODAL KUSTOM 1: AUTO IDLE LOGOUT (BISA BATAL / LANJUTKAN SESI) --}}
<div id="idle-logout-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-full text-center">
        <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent dark:border-indigo-400 dark:border-t-transparent rounded-full animate-spin mb-4"></div>
        <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Sesi Berakhir</h3>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed mb-4">Sistem otomatis keluar karena tidak ada aktivitas selama 5 menit.</p>

        {{-- Progress Bar --}}
        <div class="w-full bg-slate-100 dark:bg-slate-800 h-1 rounded-full overflow-hidden mb-5">
            <div id="idle-progress" class="bg-indigo-600 dark:bg-indigo-400 h-full w-full transition-all duration-1000 linear"></div>
        </div>

        {{-- Tombol Batal / Lanjutkan Sesi --}}
        <button onclick="batalkanAutoLogout()" class="w-full py-3 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">
            Lanjutkan Sesi (Batal)
        </button>
    </div>
</div>

{{-- MODAL KUSTOM 2: KONFIRMASI TOMBOL KELUAR MANUAL --}}
<div id="confirm-logout-modal" class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-full text-center">
        <div class="w-12 h-12 rounded-2xl bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center mb-4 text-rose-500 dark:text-rose-400">
            <i class="fa-solid fa-arrow-right-from-bracket text-xl"></i>
        </div>
        <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Keluar dari sistem?</h3>
        <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed mb-5">Sesi login Anda akan diakhiri. Anda harus memasukkan akun kembali untuk mengakses dashboard.</p>
        <div class="flex gap-2 w-full">
            <button onclick="tutupConfirmModal()" class="flex-1 py-3 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs uppercase tracking-wider hover:bg-slate-200 dark:hover:bg-slate-700 transition-all">Batal</button>
            <button onclick="eksekusiLogout()" class="flex-1 py-3 rounded-full bg-indigo-600 text-white font-bold text-xs uppercase tracking-wider hover:bg-indigo-700 shadow-md shadow-indigo-600/20 transition-all">Ya, Keluar</button>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if($userSession->role_id == 2)
<script>
const notifAudio=new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
notifAudio.preload='auto';

document.addEventListener('click',function initAudio(){
    notifAudio.play().then(()=>{
        notifAudio.pause();
        notifAudio.currentTime=0;
    }).catch(err=>console.log(err));

    document.removeEventListener('click',initAudio);
});

function playNotifSound(){
    notifAudio.currentTime=0;
    notifAudio.play()
    .then(()=>console.log('Notif bunyi'))
    .catch(err=>console.log('Audio gagal:',err));
}

let lastNotifCount = 0;
let isFirstLoad = true;
let lastReminderTime = Date.now();

function fetchNotifications(){
    fetch("{{ route('dashboard.check-notif') }}")
    .then(response=>response.json())
    .then(data=>{
        const countBadge=document.getElementById('notif-count');
        const notifDot=document.getElementById('notif-dot');
        const notifContainer=document.getElementById('notifContainer');
        const notifFooterLink=document.getElementById('notifFooterLink');

        const userRoleId = "{{ session('user')['role_id'] ?? 2 }}";
        // Tambahan: deteksi apakah dokumen saat ini menggunakan class dark mode atau tidak
        const isDarkMode = document.documentElement.classList.contains('dark');

        if(data.count>0){
            countBadge.innerText=data.count;
            countBadge.classList.remove('hidden');
            notifDot.classList.remove('hidden');

            if(notifFooterLink) {
                notifFooterLink.classList.remove('hidden');
            }

            if(notifContainer) {
                if(userRoleId == 2) {
                    // MODIFIKASI: Menambahkan latar belakang & warna teks adaptif dark mode
                    notifContainer.innerHTML = `
                        <div class="p-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex gap-3 items-start">
                            <div class="w-8 h-8 rounded-xl bg-blue-100 dark:bg-blue-950 flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                                <i class="fa-solid fa-user-clock text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Ada tamu baru menunggu konfirmasi!</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Mohon periksa manajemen antrean untuk menerima atau menolak kunjungan.</p>
                            </div>
                        </div>
                    `;
                } else {
                    // MODIFIKASI: Menambahkan latar belakang & warna teks adaptif dark mode
                    notifContainer.innerHTML = `
                        <div class="p-4 bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors flex gap-3 items-start">
                            <div class="w-8 h-8 rounded-xl bg-amber-100 dark:bg-amber-950 flex items-center justify-center text-amber-600 dark:text-amber-400 shrink-0">
                                <i class="fa-solid fa-file-signature text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">Ada disposisi kunjungan baru!</p>
                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Admin telah meneruskan data kunjungan tamu. Mohon periksa berkas untuk persetujuan.</p>
                            </div>
                        </div>
                    `;
                }
            }
        }else{
            countBadge.innerText='0';
            countBadge.classList.add('hidden');
            notifDot.classList.add('hidden');

            if(notifFooterLink) {
                notifFooterLink.classList.add('hidden');
            }

            if(notifContainer) {
                // MODIFIKASI: Menambahkan warna teks adaptif dark mode pada pesan kosong bersih
                notifContainer.innerHTML = `
                    <div class="p-6 text-center bg-white dark:bg-slate-900 text-slate-400 dark:text-slate-500">
                        <div class="w-12 h-12 rounded-full bg-slate-100 dark:bg-slate-800/60 flex items-center justify-center mx-auto mb-3">
                            <i class="fa-regular fa-bell-slash text-base text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-400">Tidak ada pemberitahuan</p>
                        <p class="text-[10px] mt-0.5 text-slate-400 dark:text-slate-500">Semua kunjungan tamu telah ditangani.</p>
                    </div>
                `;
            }
        }

        const alertTitle = (userRoleId == 2) ? 'Antrean Baru!' : 'Disposisi Baru!';
        const alertText  = (userRoleId == 2) ? `Ada ${data.count-lastNotifCount} antrean baru masuk.` : `Ada ${data.count-lastNotifCount} kunjungan tamu yang diteruskan ke Anda.`;
        const remindText = (userRoleId == 2) ? 'Masih ada antrean yang belum diproses.' : 'Masih ada disposisi tamu yang menunggu persetujuan Anda.';

        if(!isFirstLoad && data.count > lastNotifCount){
            playNotifSound();
            // MODIFIKASI: Mengonfigurasi warna background, teks, dan border SweetAlert Toast agar sinkron dengan Dark Mode
            Swal.fire({
                title: alertTitle,
                text: alertText,
                icon: 'info',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                background: isDarkMode ? '#0f172a' : '#ffffff',
                color: isDarkMode ? '#f1f5f9' : '#1e293b',
                customClass: {
                    popup: 'border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl'
                }
            });
        }

        if(data.count > 0 && data.has_pending === true){
            const now = Date.now();
            if(now - lastReminderTime >= 180000){
                playNotifSound();
                // MODIFIKASI: Mengonfigurasi warna background, teks, dan border SweetAlert Toast agar sinkron dengan Dark Mode
                Swal.fire({
                    title: (userRoleId == 2) ? 'Reminder Antrean' : 'Reminder Disposisi',
                    text: remindText,
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 5000,
                    timerProgressBar: true,
                    background: isDarkMode ? '#0f172a' : '#ffffff',
                    color: isDarkMode ? '#f1f5f9' : '#1e293b',
                    customClass: {
                        popup: 'border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl'
                    }
                });
                lastReminderTime = now;
            }
        } else {
            lastReminderTime = Date.now();
        }

        lastNotifCount=data.count;
        isFirstLoad=false;
    })
    .catch(error=>{
        console.error('Fetch notif error:',error);
    });
}

document.addEventListener('DOMContentLoaded',function(){
    fetchNotifications();
    setInterval(fetchNotifications,60000);
});
</script>
@endif

<script>
// ==================== LOGIK AUTO-LOGOUT 5 MENIT IDLE (MODAL KUSTOM MURNI) ====================
const maxIdleTimeMilidetik = 5 * 60 * 1000;
let hitungMundurBar; // Variabel global untuk menyimpan interval progress bar

function perbaruiAktivitasTerakhir() {
    localStorage.setItem('lastActivityTime', Date.now().toString());
}

let koordinatXTerakhir = -1;
let koordinatYTerakhir = -1;

function deteksiGerakanMouseAsli(event) {
    if (event.screenX === koordinatXTerakhir && event.screenY === koordinatYTerakhir) {
        return;
    }
    koordinatXTerakhir = event.screenX;
    koordinatYTerakhir = event.screenY;
    perbaruiAktivitasTerakhir();
}

const eventsUmum = ['keypress', 'mousedown', 'touchstart', 'scroll'];
eventsUmum.forEach(function(event) {
    document.addEventListener(event, perbaruiAktivitasTerakhir, false);
});
document.addEventListener('mousemove', deteksiGerakanMouseAsli, false);

if (!localStorage.getItem('lastActivityTime')) {
    perbaruiAktivitasTerakhir();
}

let infoModalSedangTerbuka = false;

// Jalankan fungsi pengecekan konstan setiap 5 detik
let cekLogoutInterval = setInterval(jalankanPengecekanIdle, 5000);

function jalankanPengecekanIdle() {
    if (infoModalSedangTerbuka) return;

    const waktuSekarang = Date.now();
    const waktuAktivitasTerakhir = parseInt(localStorage.getItem('lastActivityTime') || waktuSekarang.toString(), 10);
    const selisihWaktu = waktuSekarang - waktuAktivitasTerakhir;

    if (selisihWaktu >= maxIdleTimeMilidetik) {
        infoModalSedangTerbuka = true;

        // Tampilkan modal kustom idle
        const modalIdle = document.getElementById('idle-logout-modal');
        const progressBar = document.getElementById('idle-progress');
        progressBar.style.width = '100%'; // Setel ulang ke penuh sebelum menyusut
        modalIdle.classList.remove('hidden');

        // Jalankan animasi penyusutan progress bar (10 Detik)
        let sisaWaktuPersen = 100;
        hitungMundurBar = setInterval(() => {
            sisaWaktuPersen -= 10;
            progressBar.style.width = sisaWaktuPersen + '%';
            if (sisaWaktuPersen <= 0) {
                clearInterval(hitungMundurBar);
                eksekusiLogout();
            }
        }, 1000);
    }
}

// Fungsi Baru: Ketika admin klik tombol "Lanjutkan Sesi (Batal)"
function batalkanAutoLogout() {
    // 1. Sembunyikan modal kembali
    document.getElementById('idle-logout-modal').classList.add('hidden');

    // 2. Matikan sisa hitung mundur 10 detik progress bar
    clearInterval(hitungMundurBar);

    // 3. Reset waktu aktivitas di localStorage ke detik ini agar dianggap aktif lagi
    perbaruiAktivitasTerakhir();

    // 4. Buka kunci filter agar pengecekan idle 5 menit berjalan normal dari awal lagi
    infoModalSedangTerbuka = false;
}

// ==================== LOGIK MODAL KONFIRMASI KELUAR MANUAL ====================
function confirmLogout() {
    document.getElementById('confirm-logout-modal').classList.remove('hidden');
}

function tutupConfirmModal() {
    document.getElementById('confirm-logout-modal').classList.add('hidden');
}

function eksekusiLogout() {
    const logoutForm = document.getElementById('logout-form');
    if (logoutForm) {
        logoutForm.submit();
    } else {
        console.error('Form logout-form tidak ditemukan!');
    }
}

// ==================== GLOBAL CONTROLLER AUTO-REFRESH ====================
let isLoadingActive = false; // Flag global untuk mendeteksi sistem sedang sibuk/loading

document.addEventListener('DOMContentLoaded', function() {
    let refreshTimeLeft = 30;
    let isPaused = false;
    const timerElement = document.getElementById('refresh-timer-text');

    function aturEventInput() {
        const inputs = document.querySelectorAll('input, select, textarea');
        inputs.forEach(function(element) {
            if (element.dataset.timerEventSet) return;
            element.dataset.timerEventSet = "true";

            element.addEventListener('focus', function() {
                isPaused = true;
                // Hanya ubah teks jika sistem sedang TIDAK dalam kondisi loading data utama
                if (timerElement && !isLoadingActive) {
                    timerElement.innerText = `Auto-Refresh: Paused`;
                    timerElement.classList.remove('text-emerald-600', 'dark:text-emerald-400');
                    timerElement.classList.add('text-amber-500', 'dark:text-amber-400');
                }
            });

            element.addEventListener('blur', function() {
                isPaused = false;
                if (timerElement && !isLoadingActive) {
                    timerElement.classList.remove('text-amber-500', 'dark:text-amber-400');
                    timerElement.classList.add('text-emerald-600', 'dark:text-emerald-400');
                }
            });
        });
    }

    aturEventInput();

    const observer = new MutationObserver(aturEventInput);
    observer.observe(document.body, { childList: true, subtree: true });

    const countdownInterval = setInterval(function() {
        // TAMBAHAN KRUSIAL: Jika sedang loading data, atau input fokus, atau modal terbuka, STOP COUNTDOWN
        if (isPaused || isLoadingActive || (typeof infoModalSedangTerbuka !== 'undefined' && infoModalSedangTerbuka)) {
            return;
        }

        refreshTimeLeft--;
        if (timerElement) {
            timerElement.innerText = `Auto-Refresh: ${refreshTimeLeft}s`;
        }

        if (refreshTimeLeft <= 0) {
            clearInterval(countdownInterval);
            window.location.reload();
        }
    }, 1000);
});

// ==================== LOGIK MANAJEMEN DARK MODE ====================
const themeToggleBtn = document.getElementById('theme-toggle');
const darkIcon = document.getElementById('theme-toggle-dark-icon');
const lightIcon = document.getElementById('theme-toggle-light-icon');

function updateIcons() {
    if (document.documentElement.classList.contains('dark')) {
        lightIcon.classList.remove('hidden');
        darkIcon.classList.add('hidden');
    } else {
        darkIcon.classList.remove('hidden');
        lightIcon.classList.add('hidden');
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

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}

function toggleNotifDropdown() {
    const dropdown = document.getElementById('notifDropdown');
    if (!dropdown) return;

    if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');

        // TAMBAHKAN INI: Menahan auto-refresh agar tidak mengganggu saat melihat notif
        isModalOpen = true;

        setTimeout(() => {
            dropdown.classList.remove('scale-95', 'opacity-0');
            dropdown.classList.add('scale-100', 'opacity-100');
        }, 10);
    } else {
        dropdown.classList.remove('scale-100', 'opacity-100');
        dropdown.classList.add('scale-95', 'opacity-0');
        setTimeout(() => {
            dropdown.classList.add('hidden');

            // TAMBAHKAN INI: Jalankan kembali auto-refresh saat dropdown ditutup
            isModalOpen = false;
        }, 200);
    }
}
</script>

@stack('scripts')

</body>
</html>
