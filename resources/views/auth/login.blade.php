<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem - Politeknik Negeri Banjarmasin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
        
        // Cek tema otomatis berdasarkan sistem perangkat/landing page sebelum halaman dirender
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght=400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .glass-card { background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(12px); }
        .gradient-btn { background: linear-gradient(90deg, #6366f1 0%, #f43f5e 100%); }
        
        /* Modifikasi style radio button agar mendukung dark mode */
        .role-radio:checked + label { 
            border-color: #6366f1; 
            color: #6366f1; 
            background-color: #f5f3ff; 
            font-weight: 700; 
        }
        .dark .role-radio:checked + label { 
            border-color: #818cf8; 
            color: #a5b4fc; 
            background-color: rgba(99, 102, 241, 0.15); 
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 transition-colors duration-300">

    <div class="max-w-5xl w-full flex flex-col md:flex-row overflow-hidden rounded-3xl md:rounded-[2.5rem] shadow-2xl shadow-slate-200 dark:shadow-none border border-white dark:border-slate-800 transition-all bg-white dark:bg-slate-900">

        <div class="w-full md:w-5/12 relative p-8 md:p-12 flex flex-col justify-between text-white overflow-hidden bg-cover bg-center min-h-[320px] md:min-h-full" style="background-image: url('{{ asset('img/bg-poliban.jpg') }}');">
            <div class="absolute inset-0 bg-slate-900/80 z-0"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-600/20 rounded-full blur-3xl -mr-20 -mt-20 z-0"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('img/logo-poliban.png') }}" alt="Logo Poliban" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h2 class="font-bold text-base md:text-lg leading-tight uppercase tracking-tight">Politeknik Negeri Banjarmasin</h2>
                        <p class="text-[9px] md:text-[10px] text-indigo-300 font-medium tracking-widest uppercase">Command Center</p>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight mb-3 md:mb-4">
                    Sistem Informasi <br class="hidden md:inline"> 
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-rose-400 uppercase">Pelayanan Terpadu</span>
                </h1>
                <p class="text-slate-300 text-xs md:text-sm leading-relaxed max-w-sm md:max-w-xs uppercase tracking-wide">Kelola antrean, pantau KPI, dan tingkatkan kualitas layanan institusi dalam satu dasbor.</p>
            </div>

            <div class="relative z-10 pt-6 md:pt-10">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Digital Gate System v2.0</p>
            </div>
        </div>

        <div class="w-full md:w-7/12 bg-white dark:bg-slate-900 p-6 sm:p-10 md:p-16 relative transition-colors duration-300 flex flex-col justify-center">
            
            <div class="flex md:block mb-6 md:mb-0">
                <a href="{{ route('landing') }}" class="md:absolute md:top-8 md:right-8 text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-indigo-600 dark:hover:text-indigo-400 flex items-center gap-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="mb-6 md:mb-10">
                <div class="w-11 h-11 sm:w-12 sm:h-12 bg-indigo-100 dark:bg-indigo-950/50 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 mb-1 sm:mb-2">Selamat Datang</h2>
                <p class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm font-medium">Silakan masuk dengan akun sesuai peran Anda.</p>
            </div>

            <form id="loginForm" action="{{ route('login.post') }}" method="POST" class="space-y-4 sm:space-y-6">
                @if ($errors->any())
                    <div class="bg-red-50 dark:bg-red-950/30 text-red-500 dark:text-red-400 p-4 rounded-xl sm:rounded-2xl mb-4 sm:mb-6 text-xs sm:text-sm font-bold">
                        {{ $errors->first() }}
                    </div>
                @endif
                @csrf
                
                <div>
                    <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1 sm:mb-2">Alamat Email</label>
                    <input type="email" name="email" placeholder="nama_email@poliban.ac.id"
                           class="w-full px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-semibold text-xs sm:text-sm text-slate-700 dark:text-slate-300"
                           value="{{ old('email') }}" required>
                </div>
                
                <div>
                    <div class="flex justify-between mb-1 sm:mb-2">
                        <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Password</label>
                        <a href="#" class="text-[9px] sm:text-[10px] font-black text-rose-500 dark:text-rose-400 uppercase tracking-widest hover:underline">Lupa Password?</a>
                    </div>
                    <input type="password" name="password" placeholder="••••••••" class="w-full px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all outline-none font-semibold text-xs sm:text-sm text-slate-700 dark:text-slate-300" required>
                </div>
                
                <div>
                    <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-2 sm:mb-3">
                        Pilih Role (Akses Sistem)
                    </label>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
                        <div>
                            <input type="radio" name="role_id" id="roleAdmin" value="2" class="hidden role-radio" checked>
                            <label for="roleAdmin" class="flex items-center justify-center py-2.5 sm:py-3 px-2 border border-slate-100 dark:border-slate-800 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-tighter cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-950 transition-all text-center text-slate-700 dark:text-slate-300">
                                Admin Prodi
                            </label>
                        </div>

                        <div>
                            <input type="radio" name="role_id" id="roleLeader" value="pimpinan" class="hidden role-radio">
                            <label for="roleLeader" class="flex items-center justify-center py-2.5 sm:py-3 px-2 border border-slate-100 dark:border-slate-800 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-tighter cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-950 transition-all text-center text-slate-700 dark:text-slate-300">
                                Pimpinan
                            </label>
                        </div>

                        <div>
                            <input type="radio" name="role_id" id="roleSuper" value="1" class="hidden role-radio">
                            <label for="roleSuper" class="flex items-center justify-center py-2.5 sm:py-3 px-2 border border-slate-100 dark:border-slate-800 rounded-lg sm:rounded-xl text-[9px] sm:text-[10px] font-black uppercase tracking-tighter cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-950 transition-all text-center text-slate-700 dark:text-slate-300">
                                Super Admin
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full gradient-btn text-white py-4 sm:py-5 rounded-xl sm:rounded-[1.5rem] font-extrabold text-xs sm:text-sm uppercase tracking-widest shadow-lg shadow-indigo-200 dark:shadow-none hover:opacity-90 transition-all active:scale-95">
                    Masuk Sistem
                </button>
            </form>
        </div>
    </div>

    <div id="loading-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-full text-center">
            <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent dark:border-indigo-400 dark:border-t-transparent rounded-full animate-spin mb-4"></div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Memproses Masuk</h3>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">Mohon tunggu, sistem sedang memverifikasi akun Anda...</p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const modal = document.getElementById('loading-modal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>