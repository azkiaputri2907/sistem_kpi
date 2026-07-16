@extends('layouts.app')
<style>
    .swal2-backdrop-show {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
    }
</style>

@section('title', 'Sistem Control Panel')

@section('content')
<div class="px-4 sm:px-8 py-6 max-w-7xl mx-auto text-slate-800 dark:text-slate-100 transition-colors duration-300">

    {{-- HEADER SECTION --}}
    <div class="mb-8 pb-6 border-b border-slate-200/60 dark:border-slate-700/50">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-1">{{ $judul_dashboard }}</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pusat kendali manajemen pengguna dan konfigurasi data</p>
    </div>

    {{-- ALERT NOTIFIKASI SUCCESS --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50/80 dark:bg-emerald-950/20 border border-emerald-200/60 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-400 rounded-2xl font-bold flex items-center gap-3 text-sm transition-all shadow-sm">
        <i class="fa-solid fa-circle-check text-base text-emerald-600 dark:text-emerald-400"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- ALERT NOTIFIKASI ERROR / VALIDASI --}}
    @if($errors->any() || session('error'))
    <div class="mb-6 p-4 bg-rose-50/80 dark:bg-rose-950/20 border border-rose-200/60 dark:border-rose-900/40 text-rose-700 dark:text-rose-400 rounded-2xl font-bold flex flex-col gap-1 text-sm transition-all shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base text-rose-600 dark:text-rose-400"></i>
            <span>{{ session('error') ?? 'Terjadi kesalahan pengisian data:' }}</span>
        </div>
        @if($errors->any())
            <ul class="list-disc pl-8 mt-1 font-semibold text-xs space-y-0.5 opacity-90">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif
    </div>
    @endif

    {{-- GRID LAYOUT RESPONSIVE --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

        {{-- KARTU KIRI: MANAJEMEN PENGGUNA --}}
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-[1.5rem] md:rounded-[2.5rem] p-5 sm:p-8 shadow-md shadow-slate-100 dark:shadow-none border border-slate-100 dark:border-slate-700/50 transition-colors duration-300">

            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white">Manajemen Pengguna</h3>
                    <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-semibold mt-0.5">Total {{ count($data_users) }} akun terdaftar</p>
                </div>
                {{-- Tombol Merah Elektro dengan Icon Kuning Emas --}}
                <button onclick="openUserModal()" class="w-full sm:w-auto inline-flex justify-center items-center bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-700 text-white px-5 py-3.5 rounded-2xl text-sm font-black transition-all shadow-lg shadow-red-600/10 hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fa-solid fa-user-plus mr-2 text-amber-300 animate-pulse"></i> Tambah User
                </button>
            </div>

            {{-- LIST USER RESPONSIVE DARI SPREADSHEET --}}
            <div class="space-y-4">
                @foreach($data_users as $u)
                <div class="group flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/40 hover:bg-white dark:hover:bg-slate-800 hover:shadow-xl hover:shadow-slate-200/50 dark:hover:shadow-slate-950/50 rounded-[1.5rem] sm:rounded-[2rem] border border-transparent hover:border-slate-100 dark:hover:border-slate-700/80 transition-all gap-4">
                    <div class="flex items-center gap-4 sm:gap-5 w-full sm:w-auto">
                        {{-- Avatar dengan Gradasi Biru Malam ke Merah + Border Glow Amber --}}
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-slate-900 via-blue-950 to-red-600 rounded-xl sm:rounded-2xl flex items-center justify-center text-white shadow-md border border-amber-500/20 group-hover:border-amber-400 transition-all group-hover:scale-105 flex-shrink-0">
                            <span class="text-base sm:text-lg font-black text-amber-400 tracking-wider">{{ strtoupper(substr(data_get($u, 'name', 'U'), 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold text-slate-800 dark:text-white text-base sm:text-lg truncate group-hover:text-red-600 dark:group-hover:text-amber-400 transition-colors">{{ data_get($u, 'name') }}</p>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-1">
                                {{-- Badge Role Biru Malam / Kuning --}}
                                <span class="text-[10px] font-black px-2.5 py-0.5 bg-slate-900 dark:bg-slate-950 text-amber-400 rounded-md uppercase tracking-wider border border-amber-500/20">
                                    {{ data_get($u, 'nama_role') ?? 'Tanpa Role' }}
                                </span>
                                @if(data_get($u, 'nama_prodi'))
                                <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">•</span>
                                {{-- Badge Prodi Merah Lembut --}}
                                <span class="text-[10px] font-black px-2.5 py-0.5 bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400 rounded-md uppercase tracking-wider max-w-[140px] truncate border border-red-500/10">
                                    {{ data_get($u, 'nama_prodi') }}
                                </span>
                                @endif
                                <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">•</span>
                                <span class="text-slate-400 dark:text-slate-500 text-xs font-semibold truncate block sm:inline w-full sm:w-auto">{{ data_get($u, 'email') }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- TOMBOL AKSI CRUD USER SPREADSHEET --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t border-slate-100 dark:border-slate-700/50 sm:border-0 pt-3 sm:pt-0">
                        <button onclick="openUserModal({{ json_encode($u) }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-amber-500 dark:hover:text-amber-400 shadow-sm border border-slate-200/60 dark:border-slate-600 transition-all hover:scale-105 hover:border-amber-500/30">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        
                        {{-- Form Menggunakan Class Khusus agar Dapat Ditangkap JavaScript SweetAlert --}}
                        <form action="{{ route('control-panel.user.destroy', data_get($u, 'id')) }}" method="POST" class="delete-user-form inline">
                            @csrf 
                            @method('DELETE')
                            <button type="button" onclick="konfirmasiHapusUser(this)" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-red-600 dark:hover:text-red-400 shadow-sm border border-slate-200/60 dark:border-slate-600 transition-all hover:scale-105 hover:border-red-500/30">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- KARTU KANAN: MASTER DATA & KEAMANAN --}}
        <div class="lg:col-span-5 space-y-6 lg:space-y-8">

            {{-- DATA MASTER KEPERLUAN --}}
<div class="bg-white dark:bg-slate-800 rounded-[1.5rem] md:rounded-[2.5rem] p-5 sm:p-8 shadow-md shadow-slate-100 dark:shadow-none border border-slate-100 dark:border-slate-700/50 transition-colors duration-300">
    <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 dark:text-white mb-1">Master Keperluan</h3>
    <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-semibold mb-6">Kelola opsi tujuan kunjungan tamu dan estimasi waktunya.</p>

    {{-- FORM INPUT DIPERBARUI --}}
    <form action="{{ route('keperluan.store') }}" method="POST" onsubmit="showLoadingOverlay()" class="mb-8 space-y-3">
        @csrf
<div class="grid grid-cols-1 md:grid-cols-3 gap-3">
    {{-- Nama Keperluan (Full span) --}}
    <div class="md:col-span-1">
        <input type="text" name="keterangan" required
               class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-transparent rounded-2xl px-5 py-4 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all font-bold text-slate-800 dark:text-slate-100 text-sm"
               placeholder="Nama Keperluan...">
    </div>

    {{-- Input Angka --}}
    <div class="md:col-span-1">
        <input type="number" name="estimasi_jumlah" required min="1"
               class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-transparent rounded-2xl px-5 py-4 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all font-bold text-slate-800 dark:text-slate-100 text-sm"
               placeholder="Jumlah (Contoh: 15)">
    </div>

    {{-- Dropdown Satuan --}}
    <div class="md:col-span-1">
        <select name="estimasi_satuan" required
                class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-transparent rounded-2xl px-5 py-4 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all font-bold text-slate-800 dark:text-slate-100 text-sm">
            <option value="Menit">Menit</option>
            <option value="Jam">Jam</option>
            <option value="Hari">Hari</option>
        </select>
    </div>
</div>
        
        <button type="submit" class="w-full bg-slate-900 dark:bg-red-600 hover:bg-slate-950 dark:hover:bg-red-700 text-white px-5 py-4 rounded-2xl font-black text-sm tracking-wider uppercase transition-all shadow-md active:scale-95">
            Simpan Keperluan Baru
        </button>
    </form>

    {{-- BADGE TAG WRAPPER --}}
    <div class="flex flex-wrap gap-2.5">
        @foreach($data_keperluan as $k)
        <div class="flex items-center gap-2 pl-4 pr-2 py-2 bg-slate-50 dark:bg-slate-900/60 hover:bg-slate-100 dark:hover:bg-slate-900 text-slate-800 dark:text-slate-200 rounded-xl border border-slate-200/70 dark:border-slate-700/80 transition-all group">
            <span class="font-bold text-xs sm:text-sm">{{ $k->keterangan }}</span>
            {{-- Menampilkan estimasi waktu di samping nama keperluan --}}
            <span class="text-[10px] font-bold text-slate-400 bg-white dark:bg-slate-800 px-2 py-0.5 rounded-lg border border-slate-200 dark:border-slate-700">
                {{ $k->estimasi_waktu }}
            </span>
            
            {{-- Tombol Hapus (Pastikan route hapus sesuai dengan controller Anda) --}}
            <form action="{{ route('keperluan.destroy', $k->id) }}" method="POST" class="delete-user-form inline">
                @csrf 
                @method('DELETE')
                <button type="button" onclick="konfirmasiHapusUser(this)" class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-red-600 transition-all">
                    <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>

            {{-- KARTU PREMIUM: KEAMANAN SISTEM (Gradasi Biru Malam Mewah ke Merah Elektro Tua) --}}
            <div class="bg-gradient-to-br from-slate-900 via-blue-950 to-red-950 dark:from-slate-900 dark:via-blue-950 dark:to-slate-950 rounded-[1.5rem] md:rounded-[2.5rem] p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 dark:shadow-none relative overflow-hidden transition-all border border-amber-500/10 dark:border-slate-700/60">
                <i class="fa-solid fa-shield-halved absolute -right-4 -bottom-4 text-7xl sm:text-8xl opacity-10 dark:opacity-5 rotate-12 pointer-events-none text-amber-400"></i>
                <div class="relative z-10">
                    <h4 class="text-base sm:text-lg font-black tracking-wide uppercase mb-2 flex items-center gap-2 text-amber-400">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 animate-bounce"></i> Keamanan Sistem
                    </h4>
                    <p class="text-slate-200 dark:text-slate-400 text-xs sm:text-sm font-medium leading-relaxed opacity-90">
                        Perubahan pada halaman ini berdampak langsung pada database. Pastikan data akun dan opsi keperluan baru yang dimasukkan sudah tervalidasi dengan benar.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL INPUT DATA USER --}}
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm p-4 transition-all duration-300">
    <div id="userModalContent" class="bg-white dark:bg-slate-800 w-full max-w-md rounded-[2rem] p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-slate-700 transform scale-95 opacity-0 transition-all duration-200">

        <div class="flex justify-between items-center mb-6">
            <h4 id="modalUserTitle" class="text-xl font-black text-slate-900 dark:text-white">Tambah User Baru</h4>
            <button onclick="closeUserModal()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-red-500 dark:hover:text-red-400 rounded-full transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="modalUserForm" action="{{ route('control-panel.user.store') }}" method="POST" onsubmit="showLoadingOverlay()">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" id="input_name" name="name" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all text-sm font-bold">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" id="input_email" name="email" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all text-sm font-bold">
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Hak Akses (Role)</label>
                    <select id="select_role" name="role_id" required class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all text-sm font-bold">
                        <option value="">Pilih Role</option>
                        @foreach($rolesRaw as $r)
                            <option value="{{ data_get($r, 'id') }}">{{ data_get($r, 'nama_role') }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Program Studi (Optional)</label>
                    <select id="select_prodi" name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all text-sm font-bold">
                        <option value="">Tidak terikat Prodi (Super Admin)</option>

                        @if(isset($prodiRaw) && count($prodiRaw) > 0)
                            @foreach($prodiRaw as $p)
                                @php
                                    $idProdi = is_array($p) ? ($p['id'] ?? null) : ($p->id ?? null);
                                    $namaProdi = is_array($p) ? ($p['nama'] ?? null) : ($p->nama ?? null);
                                @endphp

                                @if(!empty($idProdi) && !empty($namaProdi))
                                    <option value="{{ $idProdi }}" data-nama="{{ $namaProdi }}">{{ $namaProdi }}</option>
                                @endif
                            @endforeach
                        @else
                            <option value="" disabled class="text-rose-500">Gagal memuat data master prodi</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Password</label>
                    <input type="text" id="input_password" name="password" class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200/60 dark:border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-red-600 dark:focus:border-amber-500 outline-none transition-all text-sm font-bold" placeholder="Ketik password...">
                    <p id="password_help" class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 hidden">*Biarkan kosong jika tidak ingin mengubah password lama.</p>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeUserModal()" class="w-1/2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 py-3 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Batal</button>
                <button type="submit" class="w-1/2 bg-slate-900 hover:bg-slate-950 dark:bg-red-600 dark:hover:bg-red-700 text-white py-3 rounded-xl text-sm font-black transition-all shadow-md">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL LOADING FULLSCREEN ( Spinner Merah - Kuning Tema Elektro ) --}}
<div id="control-panel-loading" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm select-none pointer-events-auto">
    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-[2rem] shadow-2xl flex flex-col items-center gap-4 max-w-xs mx-4 border border-slate-100 dark:border-slate-700">
        {{-- Spinner Loader Berwarna Khas Elektro --}}
        <div class="relative w-12 h-12 flex items-center justify-center">
            <div class="absolute inset-0 border-4 border-slate-100 dark:border-slate-900 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-red-600 dark:border-t-amber-400 rounded-full animate-spin"></div>
        </div>
        <div class="text-center">
            <h5 class="text-slate-900 dark:text-white font-bold text-base">Sinkronisasi ...</h5>
            <p class="text-slate-400 dark:text-slate-500 text-xs mt-1 font-medium">Mohon tunggu.</p>
        </div>
    </div>
</div>

{{-- LOGIKA JAVASCRIPT UNTUK MODAL & LOCK SCREEN --}}
<script>
    function openUserModal(user = null) {
        const modal = document.getElementById('userModal');
        const content = document.getElementById('userModalContent');
        const form = document.getElementById('modalUserForm');
        const methodField = document.getElementById('methodField');
        const title = document.getElementById('modalUserTitle');
        const pwdInput = document.getElementById('input_password');
        const pwdHelp = document.getElementById('password_help');

        form.reset();

        if (typeof stopAutoRefreshEngine === 'function') stopAutoRefreshEngine();
        if (typeof isModalOpen !== 'undefined') isModalOpen = true;

        if (user) {
            title.innerText = 'Edit Data User';
            const userId = user.hasOwnProperty('id') ? user.id : user['id'];
            form.action = '/dashboard/control-panel/user/update/' + userId;
            methodField.value = 'PUT';

            document.getElementById('input_name').value = user.hasOwnProperty('name') ? user.name : '';
            document.getElementById('input_email').value = user.hasOwnProperty('email') ? user.email : '';
            document.getElementById('select_role').value = user.hasOwnProperty('role_id') ? user.role_id : '';

            const prodiSelect = document.getElementById('select_prodi');
            const userProdi = user.hasOwnProperty('prodi_id') ? user.prodi_id : '';

            prodiSelect.value = '';

            if (userProdi) {
                prodiSelect.value = userProdi;
                if (prodiSelect.value === '') {
                    for (let option of prodiSelect.options) {
                        if (option.getAttribute('data-nama') === userProdi) {
                            prodiSelect.value = option.value;
                            break;
                        }
                    }
                }
            }

            pwdInput.required = false;
            pwdInput.value = '';
            pwdHelp.classList.remove('hidden');
        } else {
            title.innerText = 'Tambah User Baru';
            form.action = "{{ route('control-panel.user.store') }}";
            methodField.value = 'POST';

            pwdInput.required = true;
            pwdHelp.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 20);
    }

    function closeUserModal() {
        const modal = document.getElementById('userModal');
        const content = document.getElementById('userModalContent');

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            if (typeof isModalOpen !== 'undefined') isModalOpen = false;
            if (typeof startAutoRefreshEngine === 'function') startAutoRefreshEngine();
        }, 200);
    }

    function showLoadingOverlay() {
        const userModal = document.getElementById('userModal');
        if (userModal) userModal.classList.add('hidden');

        const loadingModal = document.getElementById('control-panel-loading');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
            loadingModal.classList.add('flex');
        }

        if (typeof stopAutoRefreshEngine === 'function') {
            stopAutoRefreshEngine();
        }
        return true;
    }
</script>

{{-- INJECT CDN SWEETALERT2 & LOGIKA JUGA DISINKRONKAN DENGAN BLUR CSS NYA --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function konfirmasiHapusUser(buttonElement) {
        const isDark = document.documentElement.classList.contains('dark');

        Swal.fire({
            title: 'Hapus Pengantar / User?',
            text: "Data ini juga akan dihapus secara permanen",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444', 
            cancelButtonColor: isDark ? '#475569' : '#94a3b8', 
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            background: isDark ? '#1e293b' : '#ffffff', 
            color: isDark ? '#f8fafc' : '#1e293b',
            iconColor: '#ef4444',
            customClass: {
                popup: 'rounded-[2rem] border border-slate-100 dark:border-slate-700 shadow-xl',
                title: 'font-black tracking-tight text-xl pt-2',
                htmlContainer: 'text-sm font-medium opacity-80',
                confirmButton: 'rounded-xl font-bold px-5 py-2.5 text-sm mx-1',
                cancelButton: 'rounded-xl font-bold px-5 py-2.5 text-sm text-gray-700 dark:text-gray-200 mx-1'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Berjalan beriringan, memunculkan loading spinner orisinil sistem Anda
                showLoadingOverlay();
                
                // Submit form pembuang data sheets yang membungkus button ini
                buttonElement.closest('.delete-user-form').submit();
            }
        });
    }
</script>
@endsection