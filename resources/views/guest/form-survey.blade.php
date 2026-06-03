<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survei Kepuasan Layanan</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // 1. Konfigurasi Tailwind untuk menggunakan class 'dark' (Sama seperti halaman proses)
        tailwind.config = {
            darkMode: 'class',
        }

        // 2. Skrip Cek Otomatis: Mengikuti localStorage dari halaman proses agar langsung gelap tanpa berkedip
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        /* Style untuk Rating Bintang */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 0.25rem;
        }
        @media (min-width: 640px) {
            .star-rating { gap: 0.5rem; }
        }
        .star-rating input { display: none; }
        .star-rating label {
            font-size: 2rem;
            color: #e5e7eb;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        @media (min-width: 640px) {
            .star-rating label { font-size: 2.5rem; }
        }
        
        /* Efek Hover & Active */
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #fbbf24;
            transform: scale(1.1);
        }

        /* Menyesuaikan warna dasar bintang kosong saat mode gelap aktif */
        .dark .star-rating label { color: #334155; }
        .dark .star-rating input:checked ~ label,
        .dark .star-rating label:hover,
        .dark .star-rating label:hover ~ label {
            color: #fbbf24;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6 bg-[#F1F5F9] text-slate-800 dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300">

    <div class="w-full max-w-xl space-y-4 sm:space-y-6 my-4">
        
        {{-- TOMBOL TOGGLE DARK MODE (Tetap Ada & Berfungsi) --}}
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
        <div class="bg-white dark:bg-slate-900 rounded-3xl sm:rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-800 transition-colors duration-300">
            
            {{-- Header Section --}}
            <div class="text-center mb-8 sm:mb-10">
                <div class="inline-flex items-center justify-center w-14 h-14 sm:w-16 sm:h-16 bg-indigo-50 dark:bg-indigo-950/40 rounded-2xl sm:rounded-3xl mb-4">
                    <i class="fa-solid fa-star text-xl sm:text-2xl text-indigo-500 dark:text-indigo-400"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Beri Ulasan Layanan</h2>
                <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-2 leading-relaxed">
                    Halo <span class="font-bold text-slate-700 dark:text-slate-200">{{ $nama_tamu }}</span>,
                    masukan Anda sangat berarti bagi peningkatan layanan kami. Identitas Anda tetap anonim agar Anda nyaman dalam memberikan kritik maupun saran.
                </p>
                
                @if(isset($durasi))
                    <div class="mt-4 inline-flex items-center gap-2 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/40 px-4 py-1.5 rounded-2xl">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                            Selesai dalam {{ $durasi }}
                        </span>
                    </div>
                @endif
            </div>

            <form id="surveyForm" action="{{ route('survey.store') }}" method="POST" class="space-y-8 sm:space-y-10">
                @csrf
                <input type="hidden" name="nomor_kunjungan" value="{{ $kunjungan->nomor_kunjungan }}">

                {{-- LOOP ASPEK --}}
                @foreach($aspek_survey as $aspek)
                    <div class="space-y-4">
                        <h3 class="text-indigo-500 dark:text-indigo-400 font-black uppercase tracking-widest text-[10px] text-center pt-2">
                            --- {{ $aspek->nama_aspek }} ---
                        </h3>

                        @foreach($aspek->pertanyaan as $item)
                            <div class="bg-slate-50 dark:bg-slate-800/40 p-5 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-100 dark:border-slate-800/80 shadow-inner">
                                <p class="text-slate-800 dark:text-slate-200 font-bold text-center mb-6 text-sm sm:text-base leading-relaxed">
                                    "{{ $item->pertanyaan }}"
                                </p>

                                <div class="star-rating">
                                    @for($i = 5; $i >= 1; $i--)
                                        <input type="radio" id="star-{{ $item->id }}-{{ $i }}" name="jawaban[{{ $item->id }}]" value="{{ $i }}" required>
                                        <label for="star-{{ $item->id }}-{{ $i }}">
                                            <i class="fa-solid fa-star"></i>
                                        </label>
                                    @endfor
                                </div>
                                <div class="flex justify-between mt-4 px-2 sm:px-6 text-[9px] sm:text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                                    <span>Sangat Buruk</span>
                                    <span>Sangat Puas</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                {{-- BAGIAN KRITIK & SARAN --}}
                <div class="space-y-3">
                    <h3 class="text-indigo-500 dark:text-indigo-400 font-black uppercase tracking-widest text-[10px] text-center">
                        --- Masukan Tambahan ---
                    </h3>
                    <div class="bg-slate-50 dark:bg-slate-800/40 p-5 sm:p-6 rounded-2xl sm:rounded-3xl border border-slate-100 dark:border-slate-800/80">
                        <label for="catatan" class="block text-slate-800 dark:text-slate-200 font-bold text-center mb-3 text-sm sm:text-base">
                            Kritik, Saran, atau Kesan Anda?
                        </label>
                        <textarea
                            name="catatan"
                            id="catatan"
                            rows="4"
                            class="w-full px-4 py-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 focus:ring-2 focus:ring-indigo-500/20 text-sm text-slate-700 dark:text-slate-300 placeholder-slate-400 outline-none transition-all"
                            placeholder="Tuliskan masukan Anda di sini (opsional)..."
                        ></textarea>
                    </div>
                </div>

                {{-- TOMBOL SUBMIT DENGAN PROTEKSI SPAM --}}
                <div class="pt-4">
                    <button type="submit" id="btnSubmit" class="group relative w-full inline-flex items-center justify-center px-6 py-4 sm:py-5 font-black text-white transition-all bg-indigo-600 dark:bg-indigo-500 rounded-2xl sm:rounded-3xl hover:bg-indigo-700 dark:hover:bg-indigo-600 shadow-xl shadow-indigo-100 dark:shadow-none active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100">
                        <span id="btnText" class="relative flex items-center gap-2 uppercase tracking-widest text-xs sm:text-sm">
                            Kirim Ulasan Sekarang <i class="fa-solid fa-paper-plane group-hover:translate-x-1 transition-transform ml-2"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
        <p class="text-center text-[10px] font-black text-slate-300 dark:text-slate-700 uppercase tracking-[0.6em] pt-2">Digital Gate System</p>
    </div>

    <script>
        // 1. Logika Sinkronisasi & Interaksi Tombol Toggle Dark Mode
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

        // 2. Logika Proteksi Anti-Spam (Mencegah Klik Ganda)
        document.getElementById('surveyForm').addEventListener('submit', function(e) {
            const btnSubmit = document.getElementById('btnSubmit');
            const btnText = document.getElementById('btnText');
            
            // Matikan fungsi klik tombol
            btnSubmit.disabled = true;
            
            // Ubah teks & berikan animasi loading spinner
            btnText.innerHTML = 'Mengirim Ulasan... <i class="fa-solid fa-spinner animate-spin ml-2"></i>';
        });
    </script>

    {{-- ALERT BERHASIL --}}
    @if(session('success'))
    <script>
        const isDark = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Terima Kasih!',
            text: "{{ session('success') }}",
            icon: 'success',
            confirmButtonColor: '#4f46e5',
            confirmButtonText: 'Kembali ke Beranda',
            background: isDark ? '#0f172a' : '#ffffff',
            color: isDark ? '#f8fafc' : '#0f172a',
            allowOutsideClick: false,
            customClass: {
                popup: 'rounded-3xl sm:rounded-[2.5rem]',
                confirmButton: 'rounded-full px-8 py-3 font-bold uppercase tracking-widest text-[10px]'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ url('/') }}";
            }
        });
    </script>
    @endif
</body>
</html>