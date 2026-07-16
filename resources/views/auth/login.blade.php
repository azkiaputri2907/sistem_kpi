<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem - Politeknik Negeri Banjarmasin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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

        /* Tombol Utama disesuaikan dengan Deep Blue khas landing page (#0b3a82 ke #072a63) */
        .gradient-btn { background: linear-gradient(90deg, #0b3a82 0%, #072a63 100%); }

        /* Modifikasi style radio button agar mendukung tema Deep Blue & Dark Mode */
        .role-radio:checked + label {
            border-color: #0b3a82;
            color: #0b3a82;
            background-color: #f0f5ff;
            font-weight: 700;
        }
        .dark .role-radio:checked + label {
            border-color: #3b82f6;
            color: #93c5fd;
            background-color: rgba(59, 130, 246, 0.15);
        }
        .swal2-backdrop-show {
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 min-h-screen flex items-center justify-center p-4 sm:p-6 md:p-8 transition-colors duration-300">

    <div class="max-w-5xl w-full flex flex-col md:flex-row overflow-hidden rounded-3xl md:rounded-[2.5rem] shadow-2xl shadow-slate-200 dark:shadow-none border border-white dark:border-slate-800 transition-all bg-white dark:bg-slate-900">

        <div class="w-full md:w-5/12 relative p-8 md:p-12 flex flex-col justify-between text-white overflow-hidden bg-cover bg-center min-h-[320px] md:min-h-full" style="background-image: url('{{ asset('img/bg-poliban.jpg') }}');">
            <div class="absolute inset-0 bg-slate-900/80 z-0"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-blue-600/20 rounded-full blur-3xl -mr-20 -mt-20 z-0"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <div class="w-12 h-12 md:w-14 md:h-14 flex items-center justify-center flex-shrink-0">
                        <img src="{{ asset('img/logo-poliban.png') }}" alt="Logo Poliban" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h2 class="font-bold text-base md:text-lg leading-tight uppercase tracking-tight">Politeknik Negeri Banjarmasin</h2>
                        <p class="text-[9px] md:text-[10px] text-amber-400 font-bold tracking-widest uppercase">Jurusan Teknik Elektro</p>
                    </div>
                </div>

                <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold leading-tight mb-3 md:mb-4">
                    Sistem Informasi Pelayanan Terpadu<br class="hidden md:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white to-blue-400 uppercase">SIPANDU-ELEKTRO</span>
                </h1>

                <p class="text-slate-300 text-xs md:text-sm leading-relaxed max-w-sm md:max-w-xs uppercase tracking-wide">Kelola antrean, pantau KPI, dan tingkatkan kualitas layanan institusi dalam satu dasbor.</p>
            </div>

            <div class="relative z-10 pt-6 md:pt-10">
                <p class="text-[9px] md:text-[10px] font-black text-slate-400 uppercase tracking-[0.4em]">Digital Gate System</p>
            </div>
        </div>

        <div class="w-full md:w-7/12 bg-white dark:bg-slate-900 p-6 sm:p-10 md:p-16 relative transition-colors duration-300 flex flex-col justify-center">

            <div class="flex md:block mb-6 md:mb-0">
                <a href="{{ route('landing') }}" class="md:absolute md:top-8 md:right-8 text-xs font-bold text-slate-400 dark:text-slate-500 hover:text-blue-800 dark:hover:text-blue-400 flex items-center gap-2 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Beranda
                </a>
            </div>

            <div class="mb-6 md:mb-10">
                <div class="w-11 h-11 sm:w-12 sm:h-12 bg-blue-50 dark:bg-blue-950/50 rounded-xl sm:rounded-2xl flex items-center justify-center mb-4 sm:mb-6">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-blue-800 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
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
                           class="w-full px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-blue-600/10 focus:border-blue-800 transition-all outline-none font-semibold text-xs sm:text-sm text-slate-700 dark:text-slate-300"
                           value="{{ old('email') }}" required>
                </div>

                <div>
    <div class="flex justify-between mb-1 sm:mb-2">
        <label class="block text-[9px] sm:text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Password</label>
        <a href="#" class="text-[9px] sm:text-[10px] font-black text-blue-700 dark:text-blue-400 uppercase tracking-widest hover:underline">Lupa Password?</a>
    </div>
    
    <div class="relative">
        <input type="password" id="passwordInput" name="password" placeholder="••••••••" class="w-full px-4 sm:px-6 py-3.5 sm:py-4 bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800 rounded-xl sm:rounded-2xl focus:ring-4 focus:ring-blue-600/10 focus:border-blue-800 transition-all outline-none font-semibold text-xs sm:text-sm text-slate-700 dark:text-slate-300 pr-12" required>
        
        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 dark:text-slate-500 hover:text-blue-800 dark:hover:text-blue-400 transition-colors">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <svg id="eye-off-icon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.96 9.96 0 011.563-2.687m4.61-4.61A10.05 10.05 0 0112 5c4.478 0 8.268 2.943 9.542 7a9.96 9.96 0 01-1.563 2.687m-4.61 4.61L18.364 18.364M1 1l22 22" />
            </svg>
        </button>
    </div>
</div>

                <button type="submit" class="w-full gradient-btn text-white py-4 sm:py-5 rounded-xl sm:rounded-[1.5rem] font-extrabold text-xs sm:text-sm uppercase tracking-widest shadow-lg shadow-blue-900/20 dark:shadow-none hover:opacity-95 transition-all active:scale-95">
                    Masuk Sistem
                </button>
            </form>
        </div>
    </div>

    <div id="loading-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-800 flex flex-col items-center max-w-xs w-full text-center">
            <div class="w-12 h-12 border-4 border-blue-800 border-t-transparent dark:border-blue-400 dark:border-t-transparent rounded-full animate-spin mb-4"></div>
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1">Memproses Masuk</h3>
            <p class="text-[11px] text-slate-400 dark:text-slate-500 leading-relaxed">Mohon tunggu, sistem sedang memverifikasi akun Anda...</p>
        </div>
    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eye-icon');
        const eyeOffIcon = document.getElementById('eye-off-icon');

        if (input.type === 'password') {
            input.type = 'text';
            eyeIcon.classList.add('hidden');
            eyeOffIcon.classList.remove('hidden');
        } else {
            input.type = 'password';
            eyeIcon.classList.remove('hidden');
            eyeOffIcon.classList.add('hidden');
        }
    }
</script>
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const modal = document.getElementById('loading-modal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
