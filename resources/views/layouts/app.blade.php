<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - @yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .glass-header { background: rgba(248, 250, 252, 0.8); backdrop-filter: blur(12px); }
        .card-stat { transition: all 0.3s ease; }
        .card-stat:hover { transform: translateY(-5px); }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
    </style>
</head>
<body class="flex h-screen overflow-hidden">

<aside class="w-72 bg-white border-r border-gray-100 flex flex-col shadow-sm z-20">
    <div class="p-8 flex items-center gap-3">
        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-200">
            <i class="fa-solid fa-layer-group text-xl"></i>
        </div>
        <div>
            <h1 class="font-extrabold text-gray-800 tracking-tight text-lg leading-tight">Poliban</h1>
            <p class="text-[10px] font-bold text-indigo-500 tracking-[0.2em] uppercase">Command Center</p>
        </div>
    </div>

    <div class="flex-1 px-4 overflow-y-auto">
        <nav class="space-y-2 py-4">

       {{-- MENU DASHBOARD UTAMA (Hanya Admin) --}}
            @if(in_array(Auth::user()->role_id, [1, 2]))
                <a href="{{ route('dashboard') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-bold transition">
                    <i class="fa-solid fa-chart-pie text-lg"></i> Dashboard
                </a>
            @endif

            {{-- MENU MANAJEMEN ANTREAN (Semua Role Termasuk Pimpinan Bisa Akses) --}}
            <a href="{{ route('dashboard.antrean') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard.antrean') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-semibold transition">
                <i class="fa-solid fa-users-viewfinder text-lg"></i> Manajemen Antrean
            </a>
    {{-- MENU ANALYTICS KPI - Muncul untuk Semua Role (Admin, Kajur, Kaprodi) --}}
    {{-- Pastikan mengarah ke route('dashboard.analytics') --}}
    <a href="{{ route('dashboard.analytics') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard.analytics') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-bold transition">
        <i class="fa-solid fa-chart-line text-lg"></i> Analytics KPI
    </a>

    {{-- MENU UMUM --}}
    <a href="{{ route('dashboard.ulasan') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard.ulasan') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-semibold transition">
        <i class="fa-solid fa-comment-dots text-lg"></i> Ulasan Pengunjung
    </a>

    <a href="{{ route('dashboard.laporan') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard.laporan') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-semibold transition">
        <i class="fa-solid fa-file-export text-lg"></i> Laporan & Ekspor
    </a>

            {{-- KHUSUS SUPER ADMIN: SISTEM CONTROL PANEL (Sesuai Desain Figma) --}}
            @if(Auth::user()->role_id == 1)
                <div class="pt-4 mt-4 border-t border-gray-100">
                    <p class="px-4 mb-2 text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">System Admin</p>
                    <a href="{{ route('dashboard.control_panel') }}" class="flex items-center gap-4 px-4 py-3.5 {{ request()->routeIs('dashboard.control_panel') ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:bg-gray-50' }} rounded-2xl font-bold transition">
                        <i class="fa-solid fa-gears text-lg"></i> Sistem Control Panel
                    </a>
                </div>
            @endif

        </nav>
    </div>
</aside>

<main class="flex-1 flex flex-col overflow-y-auto">

    <header class="h-24 px-10 flex items-center justify-between sticky top-0 glass-header z-10 border-b border-gray-50">
        <div class="flex items-center gap-2">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
            </span>
            <span class="text-xs font-bold text-green-600 tracking-widest uppercase">Live Monitoring</span>
        </div>

        <div class="flex items-center gap-6">
            <div class="flex items-center gap-4 border-r border-gray-200 pr-6 text-right">
                <div>
                    <p class="text-sm font-bold text-gray-800">{{ $user->name }}</p>
                    <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">
                        @if($user->role_id == 1)
                            Master Administrator
                        @elseif($user->email === 'kajur.elektro@poliban.ac.id')
                            Ketua Jurusan
                        @elseif($user->role_id == 2)
                            Admin Unit
                        @else
                            Ketua Program Studi
                        @endif
                    </p>
                </div>
                <div class="w-12 h-12 bg-indigo-600 border-4 border-indigo-50 shadow-sm rounded-2xl flex items-center justify-center text-white font-black text-sm uppercase">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
            </div>

            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                @csrf
                <button type="button" onclick="confirmLogout()" class="w-12 h-12 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center shadow-sm group">
                    <i class="fa-solid fa-right-from-bracket text-lg group-hover:scale-110 transition-transform"></i>
                </button>
            </form>
        </div>
    </header>

    <div class="px-10 py-10">
        @yield('content')
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmLogout() {
        Swal.fire({
            title: 'Keluar dari Sistem?',
            text: "Sesi Anda akan berakhir.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Ya, Logout',
            cancelButtonText: 'Batal',
            customClass: { popup: 'rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) { document.getElementById('logout-form').submit(); }
        })
    }
</script>
@stack('scripts')
</body>
</html>
