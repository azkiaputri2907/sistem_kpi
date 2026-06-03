@extends('layouts.app')

@section('title', 'Sistem Control Panel')

@section('content')
<div class="px-4 sm:px-8 py-6 max-w-7xl mx-auto text-slate-800 dark:text-slate-100 transition-colors duration-300">
    
    {{-- HEADER SECTION --}}
    <div class="mb-8 pb-6 border-b border-slate-100 dark:border-slate-700/50">
        <h2 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-1">{{ $judul_dashboard }}</h2>
        <p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Pusat kendali manajemen pengguna dan konfigurasi data master berbasis Google Sheets.</p>
    </div>

    {{-- ALERT NOTIFIKASI SUCCESS --}}
    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-2xl font-bold flex items-center gap-3 text-sm transition-all shadow-sm">
        <i class="fa-solid fa-circle-check text-base"></i>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    {{-- ALERT NOTIFIKASI ERROR / VALIDASI --}}
    @if($errors->any() || session('error'))
    <div class="mb-6 p-4 bg-rose-50 dark:bg-rose-950/30 border border-rose-100 dark:border-rose-900/50 text-rose-600 dark:text-rose-400 rounded-2xl font-bold flex flex-col gap-1 text-sm transition-all shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fa-solid fa-circle-exclamation text-base"></i>
            <span>{{ session('error') ?? 'Terjadi kesalahan pengisian data:' }}</span>
        </div>
        @if($errors->any())
            <ul class="list-disc pl-8 mt-1 font-semibold text-xs space-y-0.5">
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
        <div class="lg:col-span-7 bg-white dark:bg-slate-800 rounded-[1.5rem] md:rounded-[2.5rem] p-5 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-700/60 transition-colors duration-300">
            
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                <div>
                    <h3 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white">Manajemen Pengguna</h3>
                    <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-medium">Total {{ count($data_users) }} akun terdaftar</p>
                </div>
                <button onclick="openUserModal()" class="w-full sm:w-auto inline-flex justify-center items-center bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white px-5 py-3 rounded-2xl text-sm font-black transition-all shadow-lg shadow-indigo-100 dark:shadow-none hover:scale-[1.02] active:scale-[0.98]">
                    <i class="fa-solid fa-user-plus mr-2"></i> Tambah User
                </button>
            </div>

            {{-- LIST USER RESPONSIVE DARI SPREADSHEET --}}
            <div class="space-y-4">
                @foreach($data_users as $u)
                <div class="group flex flex-col sm:flex-row items-start sm:items-center justify-between p-4 sm:p-5 bg-slate-50 dark:bg-slate-900/50 hover:bg-white dark:hover:bg-slate-800 hover:shadow-xl dark:hover:shadow-slate-950/40 rounded-[1.5rem] sm:rounded-[2rem] border border-transparent hover:border-slate-100 dark:hover:border-slate-700/60 transition-all gap-4">
                    <div class="flex items-center gap-4 sm:gap-5 w-full sm:w-auto">
                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl sm:rounded-2xl flex items-center justify-center text-white shadow-md shadow-indigo-100 dark:shadow-none group-hover:scale-105 transition-transform flex-shrink-0">
                            <span class="text-base sm:text-lg font-black">{{ strtoupper(substr(data_get($u, 'name', 'U'), 0, 1)) }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-800 dark:text-white text-base sm:text-lg truncate">{{ data_get($u, 'name') }}</p>
                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 mt-0.5">
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 rounded-md uppercase tracking-wider">
                                    {{ data_get($u, 'nama_role') ?? 'Tanpa Role' }}
                                </span>
                                @if(data_get($u, 'nama_prodi'))
                                <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">•</span>
                                <span class="text-[10px] font-bold px-2 py-0.5 bg-purple-100 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400 rounded-md uppercase tracking-wider max-w-[140px] truncate">
                                    {{ data_get($u, 'nama_prodi') }}
                                </span>
                                @endif
                                <span class="text-slate-300 dark:text-slate-600 hidden sm:inline">•</span>
                                <span class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm font-medium truncate block sm:inline w-full sm:w-auto">{{ data_get($u, 'email') }}</span>
                            </div>
                        </div>
                    </div>
                    
                    {{-- TOMBOL AKSI CRUD USER SPREADSHEET --}}
                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end border-t border-slate-100 dark:border-slate-700/50 sm:border-0 pt-3 sm:pt-0">
                        <button onclick="openUserModal({{ json_encode($u) }})" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 shadow-sm border border-slate-100 dark:border-slate-600 transition-all hover:scale-105">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <form action="{{ route('control-panel.user.destroy', data_get($u, 'id')) }}" method="POST" onsubmit="return confirm('Hapus user ini dari Google Sheets?') && showLoadingOverlay();" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-10 h-10 flex items-center justify-center rounded-xl bg-white dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 shadow-sm border border-slate-100 dark:border-slate-600 transition-all hover:scale-105">
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
            <div class="bg-white dark:bg-slate-800 rounded-[1.5rem] md:rounded-[2.5rem] p-5 sm:p-8 shadow-sm border border-slate-100 dark:border-slate-700/60 transition-colors duration-300">
                <h3 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-white mb-1">Master Keperluan</h3>
                <p class="text-xs sm:text-sm text-slate-400 dark:text-slate-500 font-medium mb-6">Kelola opsi tujuan kunjungan tamu.</p>

                {{-- FORM INPUT DENGAN FITUR SHOW LOADING --}}
                <form action="{{ route('keperluan.store') }}" method="POST" onsubmit="showLoadingOverlay()" class="relative mb-8 flex flex-col sm:block gap-3">
                    @csrf
                    <input type="text" name="keterangan" required
                           class="w-full bg-slate-50 dark:bg-slate-900 border-2 border-transparent rounded-2xl px-5 py-4 pr-4 sm:pr-28 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all font-semibold text-slate-700 dark:text-slate-200 placeholder-slate-400 text-sm sm:text-base"
                           placeholder="Ketik keperluan baru...">
                    <button type="submit" class="w-full sm:w-auto sm:absolute sm:right-2 sm:top-2 sm:bottom-2 bg-slate-900 dark:bg-indigo-500 hover:bg-black dark:hover:bg-indigo-600 text-white px-5 py-3 sm:py-0 rounded-xl font-black text-sm tracking-wider uppercase transition-all shadow-md active:scale-95 sm:active:scale-100">
                        Simpan
                    </button>
                </form>

                {{-- BADGE TAG WRAPPER --}}
                <div class="flex flex-wrap gap-2.5">
                    @foreach($data_keperluan as $k)
                    <div class="flex items-center gap-2 pl-4 pr-2 py-2 bg-indigo-50/50 dark:bg-indigo-950/40 hover:bg-indigo-50 dark:hover:bg-indigo-950/70 text-indigo-700 dark:text-indigo-300 rounded-xl border border-indigo-100/50 dark:border-indigo-900/40 transition-all group">
                        <span class="font-bold text-xs sm:text-sm">{{ $k->keterangan }}</span>
                        <form action="{{ route('keperluan.destroy', $k->id) }}" method="POST" onsubmit="showLoadingOverlay()" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="w-6 h-6 flex items-center justify-center rounded-lg bg-white dark:bg-slate-800 text-indigo-300 dark:text-indigo-500 hover:text-rose-500 dark:hover:text-rose-400 shadow-sm dark:shadow-none hover:shadow-md transition-all">
                                <i class="fa-solid fa-xmark text-[10px]"></i>
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- KARTU PREMIUM: KEAMANAN SISTEM --}}
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 dark:from-indigo-950 dark:to-slate-900 rounded-[1.5rem] md:rounded-[2.5rem] p-6 sm:p-8 text-white shadow-xl shadow-indigo-100 dark:shadow-none relative overflow-hidden transition-all border border-transparent dark:border-slate-700/50">
                <i class="fa-solid fa-shield-halved absolute -right-4 -bottom-4 text-7xl sm:text-8xl opacity-10 dark:opacity-5 rotate-12 pointer-events-none"></i>
                <div class="relative z-10">
                    <h4 class="text-base sm:text-lg font-black tracking-wide uppercase mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400"></i> Keamanan Sistem
                    </h4>
                    <p class="text-indigo-100 dark:text-slate-400 text-xs sm:text-sm font-medium leading-relaxed">
                        Perubahan pada halaman ini berdampak langsung pada database master Spreadsheet. Pastikan data akun dan opsi keperluan baru yang dimasukkan sudah tervalidasi dengan benar.
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- MODAL INPUT DATA USER (TAMBAH & EDIT ROW SPREADSHEET) --}}
<div id="userModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 backdrop-blur-sm p-4 transition-all duration-300">
    <div id="userModalContent" class="bg-white dark:bg-slate-800 w-full max-w-md rounded-[2rem] p-6 sm:p-8 shadow-2xl border border-slate-100 dark:border-slate-700 transform scale-95 opacity-0 transition-all duration-200">
        
        <div class="flex justify-between items-center mb-6">
            <h4 id="modalUserTitle" class="text-xl font-black text-slate-800 dark:text-white">Tambah User Baru</h4>
            <button onclick="closeUserModal()" class="w-8 h-8 flex items-center justify-center bg-slate-100 dark:bg-slate-700 text-slate-400 dark:text-slate-300 hover:text-rose-500 dark:hover:text-rose-400 rounded-full transition-colors">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="modalUserForm" action="{{ route('control-panel.user.store') }}" method="POST" onsubmit="showLoadingOverlay()">
            @csrf
            <input type="hidden" id="methodField" name="_method" value="POST">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Nama Lengkap</label>
                    <input type="text" id="input_name" name="name" required class="w-full bg-slate-50 dark:bg-slate-900 border border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 outline-none transition-all text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Alamat Email</label>
                    <input type="email" id="input_email" name="email" required class="w-full bg-slate-50 dark:bg-slate-900 border border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 outline-none transition-all text-sm font-semibold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Hak Akses (Role)</label>
                    <select id="select_role" name="role_id" required class="w-full bg-slate-50 dark:bg-slate-900 border border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 outline-none transition-all text-sm font-semibold">
                        <option value="">Pilih Role</option>
                        @foreach($rolesRaw as $r)
                            <option value="{{ data_get($r, 'id') }}">{{ data_get($r, 'nama_role') }}</option>
                        @endforeach
                    </select>
                </div>

<div>
    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Program Studi (Optional)</label>
    <select id="select_prodi" name="prodi_id" class="w-full bg-slate-50 dark:bg-slate-900 border border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 outline-none transition-all text-sm font-semibold">
        <option value="">Tidak terikat Prodi (Super Admin)</option>
        
        @if(isset($prodiRaw) && count($prodiRaw) > 0)
            @foreach($prodiRaw as $p)
                @php
                    $idProdi = is_array($p) ? ($p['id'] ?? null) : ($p->id ?? null);
                    $namaProdi = is_array($p) ? ($p['nama'] ?? null) : ($p->nama ?? null);
                @endphp
                
                @if(!empty($idProdi) && !empty($namaProdi))
                    {{-- Tambahkan atribut data-nama agar javascript bisa mencocokkan jika data di sheet berupa Teks Nama --}}
                    <option value="{{ $idProdi }}" data-nama="{{ $namaProdi }}">{{ $namaProdi }}</option>
                @endif
            @endforeach
        @else
            <option value="" disabled class="text-rose-500">Gagal memuat data master prodi</option>
        @endif
    </select>
</div>
                <div>
                    <label id="label_password" class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Password</label>
                    <input type="text" id="input_password" name="password" class="w-full bg-slate-50 dark:bg-slate-900 border border-transparent rounded-xl px-4 py-3 text-slate-800 dark:text-slate-100 focus:bg-white dark:focus:bg-slate-800 focus:border-indigo-500 outline-none transition-all text-sm font-semibold" placeholder="Ketik password...">
                    <p id="password_help" class="text-[11px] text-slate-400 dark:text-slate-500 mt-1 hidden">*Biarkan kosong jika tidak ingin mengubah password lama di Spreadsheet.</p>
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button type="button" onclick="closeUserModal()" class="w-1/2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 py-3 rounded-xl text-sm font-bold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">Batal</button>
                <button type="submit" class="w-1/2 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl text-sm font-black transition-colors shadow-md">Simpan ke Sheets</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL LOADING FULLSCREEN (KUNCI INTERAKSI GLOBAL) --}}
<div id="control-panel-loading" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-slate-950/60 backdrop-blur-sm select-none pointer-events-auto">
    <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-[2rem] shadow-2xl flex flex-col items-center gap-4 max-w-xs mx-4 border border-slate-100 dark:border-slate-700">
        {{-- Spinner Loader --}}
        <div class="relative w-12 h-12 flex items-center justify-center">
            <div class="absolute inset-0 border-4 border-indigo-100 dark:border-indigo-950 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-t-indigo-600 dark:border-t-indigo-400 rounded-full animate-spin"></div>
        </div>
        <div class="text-center">
            <h5 class="text-slate-900 dark:text-white font-bold text-base">Sinkronisasi Google Sheets...</h5>
            <p class="text-slate-400 dark:text-slate-500 text-xs mt-1 font-medium">Mohon tunggu, server sedang menulis data langsung ke baris dokumen Spreadsheet Anda.</p>
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
            // MODE UPDATE DATA USER
            title.innerText = 'Edit Data User (Sheets)';
            const userId = user.hasOwnProperty('id') ? user.id : user['id'];
            form.action = '/dashboard/control-panel/user/update/' + userId; 
            methodField.value = 'PUT';
            
            document.getElementById('input_name').value = user.hasOwnProperty('name') ? user.name : '';
            document.getElementById('input_email').value = user.hasOwnProperty('email') ? user.email : '';
            document.getElementById('select_role').value = user.hasOwnProperty('role_id') ? user.role_id : '';
            
            // LOGIKA CERDAS UNTUK SELECT PRODI (Bisa membaca ID Angka maupun Teks Nama Lama)
            const prodiSelect = document.getElementById('select_prodi');
            const userProdi = user.hasOwnProperty('prodi_id') ? user.prodi_id : '';
            
            prodiSelect.value = ''; // Reset default awal
            
            if (userProdi) {
                // Coba set berdasarkan ID angka dulu
                prodiSelect.value = userProdi;
                
                // Jika tidak terpilih (artinya userProdi di sheet berupa Teks Nama, bukan angka)
                if (prodiSelect.value === '') {
                    // Cari option yang atribut data-nama nya cocok dengan teks dari sheet
                    for (let option of prodiSelect.options) {
                        if (option.getAttribute('data-nama') === userProdi) {
                            prodiSelect.value = option.value; // Set ke ID angka prodi tersebut
                            break;
                        }
                    }
                }
            }
            
            pwdInput.required = false;
            pwdInput.value = ''; 
            pwdHelp.classList.remove('hidden');
        } else {
            // MODE STORE USER BARU
            title.innerText = 'Tambah User Baru (Sheets)';
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
@endsection