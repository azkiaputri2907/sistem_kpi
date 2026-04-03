<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu Digital & Antrean</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Animasi halus untuk hover */
        .transition-all { transition: all 0.3s ease; }
    </style>
</head>
<body class="bg-[#F8FAFC] min-h-screen flex flex-col text-slate-800 relative">

    <header class="w-full px-6 lg:px-12 py-6 flex justify-between items-center max-w-[90rem] mx-auto">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-xl shadow-md">
                P
            </div>
            <div>
                <h2 class="font-extrabold text-slate-900 text-lg lg:text-xl leading-tight tracking-tight">Politeknik Negeri Banjarmasin</h2>
                <p class="text-xs lg:text-sm text-slate-500 font-medium">Sistem Pelayanan Terpadu</p>
            </div>
        </div>

        <a href="{{ route('login') }}" class="hidden sm:inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 rounded-full text-sm font-bold text-slate-700 hover:bg-slate-50 hover:text-blue-600 hover:border-blue-200 transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
            Login Admin
        </a>

        <a href="{{ route('login') }}" class="sm:hidden inline-flex items-center justify-center w-10 h-10 bg-white border border-slate-200 rounded-full text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-all shadow-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
            </svg>
        </a>
    </header>

    <main class="flex-grow flex items-center justify-center p-4 lg:p-8 w-full">
        <div class="max-w-7xl w-full grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            <div class="flex flex-col justify-center order-2 lg:order-1 px-4 lg:px-0">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white shadow-sm border border-slate-100 text-xs font-semibold text-slate-700 w-max mb-8">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Sistem Online Beroperasi
                </div>

                <h1 class="text-5xl lg:text-6xl font-extrabold leading-[1.1] mb-6 text-slate-900 tracking-tight">
                    Layanan Publik <br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-500">Jurusan Teknik Elektro</span>
                </h1>

                <p class="text-slate-500 mb-10 text-lg leading-relaxed max-w-md">
                    Dapatkan nomor antrean Anda sekarang dan pantau estimasi waktu (SLA) secara real-time.
                </p>

                <div class="bg-white p-2 rounded-full shadow-lg border border-slate-100 flex items-center max-w-md relative z-10">
                    <div class="pl-4 pr-2 text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 font-bold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input type="text" id="inputLacak" placeholder="CEK NOMOR ANTREAN (MIS: IN-123)" class="w-full py-3 bg-transparent text-slate-700 font-bold outline-none uppercase placeholder:font-normal placeholder:text-slate-400 text-sm">
                    <button type="button" onclick="lacakAntrean()" class="bg-gradient-to-r from-purple-500 to-orange-400 text-white font-bold px-8 py-3 rounded-full hover:shadow-lg hover:shadow-purple-500/30 transition-all active:scale-95">
                        Lacak
                    </button>
                </div>

                <div class="mt-12 h-48 w-full max-w-md rounded-3xl bg-gradient-to-tr from-blue-100 via-purple-50 to-orange-50 relative overflow-hidden flex items-center justify-center border border-white shadow-inner">
                    <span class="text-slate-400 font-medium text-sm">Pelacakan Real-Time Terintegrasi</span>
                    <div class="absolute -bottom-8 -left-8 w-24 h-24 bg-blue-500/20 rounded-full blur-xl"></div>
                    <div class="absolute top-4 -right-4 w-16 h-16 bg-orange-400/20 rounded-full blur-lg"></div>
                </div>
            </div>

            <div class="order-1 lg:order-2 bg-white p-8 lg:p-10 rounded-[2rem] shadow-2xl shadow-slate-200/50 border border-slate-50">
                <h2 class="text-2xl font-bold mb-2 text-slate-900">Buku Tamu Digital</h2>
                <p class="text-sm text-slate-500 mb-8">Isi form di bawah ini untuk memulai layanan kunjungan Anda.</p>

                @if(session('success'))
                <div class="mb-8 bg-emerald-50 text-emerald-700 p-4 rounded-xl font-medium border border-emerald-100 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    {{ session('success') }}
                </div>
                @endif

                @if ($errors->any())
                <div class="mb-8 bg-red-50 text-red-700 p-4 rounded-xl text-sm border border-red-100">
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

                <form action="{{ route('kunjungan.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Lengkap</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder:text-slate-400" placeholder="Jhon Doe">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">NIM/NIP/NIK <span class="text-slate-400 font-normal">(Opsional)</span></label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" name="identitas_no" value="{{ old('identitas_no') }}" class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder:text-slate-400" placeholder="C03...">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">No. WhatsApp</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" /></svg>
                                </div>
                                <input type="text" name="no_telepon" value="{{ old('no_telepon') }}" required class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder:text-slate-400" placeholder="0812...">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Asal Instansi / Kategori</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /></svg>
                                </div>
                                <input type="text" name="asal_instansi" value="{{ old('asal_instansi') }}" required class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 placeholder:text-slate-400" placeholder="Mis: Poliban">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Tujuan (Prodi/Bagian)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd" /></svg>
                                </div>
                                <select name="prodi_id" required class="w-full pl-11 pr-10 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none cursor-pointer">
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
    <label class="block text-sm font-semibold text-slate-700 mb-2">Kategori Keperluan</label>
    <div class="relative">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v3.57A22.952 22.952 0 0110 13a22.95 22.95 0 01-8-1.43V8a2 2 0 012-2h2zm2-1a1 1 0 011-1h2a1 1 0 011 1v1H8V5zm1 5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                <path d="M2 13.692V16a2 2 0 002 2h12a2 2 0 002-2v-2.308A24.974 24.974 0 0110 15c-2.796 0-5.487-.46-8-1.308z" />
            </svg>
        </div>
        <select name="keperluan_id" required class="w-full pl-11 pr-10 py-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 appearance-none cursor-pointer">
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
</div>                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">Keperluan (Detail)</label>
                        <textarea name="catatan_keperluan" rows="3" class="w-full p-4 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-100 focus:border-blue-500 transition-all outline-none text-slate-700 resize-none placeholder:text-slate-400" placeholder="Ceritakan singkat tujuan kedatangan Anda...">{{ old('catatan_keperluan') }}</textarea>
                    </div>

                    <button type="submit" class="w-full py-4 mt-4 bg-gradient-to-r from-blue-600 via-purple-500 to-orange-400 text-white font-bold text-lg rounded-full hover:shadow-lg hover:shadow-purple-500/30 transition-all hover:-translate-y-0.5">
                        Daftar Kunjungan
                    </button>
                </form>
            </div>
        </div>
    </main>
    <section class="w-full px-6 lg:px-12 py-16 max-w-[90rem] mx-auto text-center relative z-10">
        <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Pelayanan Cerdas Terintegrasi</h2>
        <p class="text-slate-500 mb-12 max-w-2xl mx-auto text-sm md:text-base">
            Sistem kami dirancang khusus untuk memberikan pengalaman layanan yang transparan, terukur, dan responsif.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-left">
            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-3 text-lg">Pelacakan Real-Time & SLA</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Cek nomor antrean untuk memantau status layanan dan estimasi waktu selesai tanpa perlu login.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-3 text-lg">Notifikasi</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Integrasi WhatsApp/Email otomatis untuk mempercepat persetujuan layanan oleh pimpinan.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-3 text-lg">Survei Kepuasan Anonim</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Sistem penilaian pelayanan terintegrasi dengan identitas yang disamarkan untuk menjaga privasi.</p>
            </div>

            <div class="bg-white p-8 rounded-3xl shadow-sm border border-slate-100 hover:shadow-xl transition-all">
                <div class="w-12 h-12 rounded-full bg-purple-50 flex items-center justify-center text-purple-500 mb-6">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 mb-3 text-lg">Aman & Responsif</h3>
                <p class="text-slate-500 text-sm leading-relaxed">Akses mudah dari semua perangkat (tanpa instal aplikasi) dengan keamanan sesi Auto-Logout.</p>
            </div>
        </div>
    </section>

    <footer class="w-full px-6 lg:px-12 py-8 mt-auto text-slate-500 border-t border-slate-200">
        <div class="max-w-[90rem] mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-xs font-medium">
            <div class="text-center md:text-left">
                <p class="font-bold text-slate-800 mb-1">Sistem Informasi Pelayanan Publik & Monitoring KPI</p>
                <p>&copy; 2026 Jurusan Teknik Elektro - Politeknik Negeri Banjarmasin</p>
            </div>
            <div class="flex gap-6 items-center">
                <a href="#" class="hover:text-blue-600 transition-colors">Privasi & Keamanan</a>
                <a href="#" class="hover:text-blue-600 transition-colors">Panduan Layanan</a>
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

    // Gunakan helper route() dari Laravel agar URL selalu dinamis dan benar
    // Ini akan otomatis menghasilkan /kunjungan/NOMOR
    const urlTujuan = "{{ route('kunjungan.status', ':nomor') }}".replace(':nomor', nomorKunjungan);

    window.location.href = urlTujuan;
}

    document.getElementById('inputLacak').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            lacakAntrean();
        }
    });
</script>
</body>
</html>
