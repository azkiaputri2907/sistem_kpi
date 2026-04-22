@extends('layouts.app')

@section('title', 'Manajemen Antrean')

@section('content')
    {{-- SISTEM NOTIFIKASI TOAST POP-UP --}}
    <div id="toast-container" class="fixed bottom-10 right-10 z-[999] flex flex-col gap-4">
        @if(session('success'))
            <div class="toast-item bg-emerald-500 text-white px-8 py-5 rounded-[2rem] shadow-[0_20px_50px_rgba(16,185,129,0.3)] flex items-center gap-4 animate-toast-in">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-check text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Berhasil</p>
                    <p class="font-bold text-sm">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="toast-item bg-rose-500 text-white px-8 py-5 rounded-[2rem] shadow-[0_20px_50px_rgba(244,63,94,0.3)] flex items-center gap-4 animate-toast-in">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest opacity-70">Gagal</p>
                    <p class="font-bold text-sm">{{ session('error') }}</p>
                </div>
            </div>
        @endif
    </div>

    <div class="flex justify-between items-end mb-10">
        <div>
            <h2 class="text-4xl font-black text-gray-800 tracking-tight leading-none">Manajemen Antrean</h2>
            <p class="text-slate-400 text-sm font-medium mt-3">Monitor dan kelola riwayat antrean secara mendetail.</p>
        </div>
        <div class="flex gap-4">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                <input type="text" placeholder="Cari pengunjung..." class="pl-12 pr-6 py-3 bg-white border border-gray-100 rounded-2xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none shadow-sm w-64 transition-all">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[2.5rem] border border-gray-50 shadow-sm overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50">
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">ID</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Pengunjung</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status Layanan</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Estimasi SLA</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status SLA</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Status Pimpinan</th>
                    <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($data_kunjungan as $k)
                <tr class="hover:bg-slate-50/50 transition-colors group">
                    <td class="px-8 py-6 font-bold text-gray-800">#{{ $k->nomor_kunjungan }}</td>

                    <td class="px-8 py-6">
                        <p class="font-extrabold text-gray-800">{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}</p>
                        <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-tighter">{{ $k->pengunjung->instansi ?? '-' }}</p>
                        @if($k->keperluan)
                            <div class="mt-2.5 p-2.5 bg-slate-50 border border-dashed border-slate-200 rounded-xl max-w-[250px]">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1 italic">Detail Keperluan:</p>
                                <p class="text-[11px] text-slate-600 leading-relaxed font-medium">
                                    "{{ Str::limit($k->keperluan, 100, '...') }}"
                                </p>
                            </div>
                        @endif
                    </td>

                    <td class="px-8 py-6 text-center">
                        @php
                            $color = match($k->status_layanan) {
                                'Selesai' => 'bg-emerald-100 text-emerald-600',
                                'Diproses' => 'bg-indigo-100 text-indigo-600',
                                'Ditolak' => 'bg-rose-100 text-rose-600',
                                default => 'bg-amber-100 text-amber-600'
                            };
                        @endphp
                        <span class="px-4 py-1.5 {{ $color }} rounded-full text-[9px] font-black uppercase tracking-widest inline-block">
                            {{ $k->status_layanan }}
                        </span>
                    </td>

                    <td class="px-8 py-6 text-center text-sm font-bold text-gray-600">
                        {{ $k->estimasi_sla ?? '-' }} {{ $k->satuan_sla ?? '' }}
                    </td>

                    <td class="px-8 py-6 text-center">
                        @if($k->status_layanan == 'Selesai')
                            @if($k->status_sla == 'Tepat Waktu')
                                <span class="text-emerald-500 font-black text-[10px] flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-circle-check"></i> TEPAT WAKTU
                                </span>
                            @elseif($k->status_sla == 'Terlambat')
                                <span class="text-rose-500 font-black text-[10px] flex items-center justify-center gap-1">
                                    <i class="fa-solid fa-circle-exclamation"></i> TERLAMBAT
                                </span>
                            @else
                                <span class="text-gray-300 text-[10px] italic">Data SLA Kosong</span>
                            @endif
                        @else
                            <span class="text-indigo-400 text-[9px] font-black uppercase italic tracking-tighter">Sedang Berjalan</span>
                        @endif
                    </td>

                    <td class="px-8 py-6 text-center">
                        @if(in_array(Auth::user()->role_id, [3, 4]) && ($k->status_pimpinan == 'Menunggu' || empty($k->status_pimpinan)))
                            <div class="flex justify-center gap-2">
                                <button type="button" onclick="bukaModalTanggapan('{{ $k->id }}', 'Disetujui')" class="px-3 py-1.5 bg-emerald-500 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-emerald-600 transition-all shadow-sm">
                                    <i class="fa-solid fa-check mr-1"></i> Terima
                                </button>
                                <button type="button" onclick="bukaModalTanggapan('{{ $k->id }}', 'Ditolak')" class="px-3 py-1.5 bg-rose-500 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:bg-rose-600 transition-all shadow-sm">
                                    <i class="fa-solid fa-xmark mr-1"></i> Tolak
                                </button>
                            </div>
                        @else
                            @php
                                $pimpinanColor = match($k->status_pimpinan) {
                                    'Disetujui' => 'bg-emerald-100 text-emerald-600',
                                    'Ditolak' => 'bg-rose-100 text-rose-600',
                                    default => 'bg-amber-100 text-amber-600'
                                };
                            @endphp
                            <div class="flex flex-col items-center gap-1">
                                <span class="px-4 py-1.5 {{ $pimpinanColor }} rounded-full text-[9px] font-black uppercase tracking-widest inline-block">
                                    {{ $k->status_pimpinan ?? 'Menunggu' }}
                                </span>
                                @if($k->catatan_pimpinan)
                                    <p class="text-[9px] text-gray-400 italic mt-1">"{{ Str::limit($k->catatan_pimpinan, 20) }}"</p>
                                @endif
                            </div>
                        @endif
                    </td>

                    <td class="px-8 py-6 text-center">
                        <div class="flex justify-center gap-2">
                            <a href="{{ url('/status/'.$k->nomor_kunjungan) }}" target="_blank" class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-400 rounded-xl hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                                <i class="fa-solid fa-eye text-xs"></i>
                            </a>

                            @if(Auth::user()->role_id == 2)
                                @if($k->status_layanan != 'Selesai')
                                    <button type="button" onclick="bukaModalEmail('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}', '{{ addslashes($k->keperluan) }}')"
                                            class="w-9 h-9 flex items-center justify-center bg-blue-50 text-blue-500 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Teruskan ke Pimpinan">
                                        <i class="fa-solid fa-envelope text-xs"></i>
                                    </button>
                                @endif

                                @if($k->status_layanan == 'Antre')
                                    <button type="button"
        onclick="bukaModalProsesSLA('{{ $k->nomor_kunjungan }}')"
        class="w-9 h-9 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm">
    <i class="fa-solid fa-play text-xs"></i>
</button>
                                @elseif($k->status_layanan == 'Diproses')
                                    <form action="{{ route('kunjungan.selesai', $k->id) }}" method="POST" class="m-0" onsubmit="return confirm('Selesaikan layanan tanpa lampiran file?')">
                                        @csrf
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Selesaikan Manual">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </form>

                                    <button type="button" onclick="bukaModalUpload('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')"
                                            class="w-9 h-9 flex items-center justify-center bg-amber-50 text-amber-600 rounded-xl hover:bg-amber-500 hover:text-white transition-all shadow-sm" title="Selesaikan & Upload File">
                                        <i class="fa-solid fa-paperclip text-xs"></i>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-8 py-20 text-center text-gray-400">Data tidak ditemukan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL ESTIMASI SLA (MULAI PROSES) --}}
   {{-- MODAL ESTIMASI SLA (MULAI PROSES) --}}
    <div id="modalProsesSLA" class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-all px-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl animate-modal-up relative">

            {{-- Header & Tombol Tutup --}}
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-black text-gray-800 tracking-tight">Estimasi Waktu</h3>
                <button type="button" onclick="tutupModalSLA()" class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 text-gray-400 hover:bg-rose-50 hover:text-rose-500 transition-all group">
                    <i class="fa-solid fa-xmark text-xl group-hover:rotate-90 transition-transform"></i>
                </button>
            </div>

            {{-- Pesan Peringatan --}}
            <div class="bg-amber-50 border-l-4 border-amber-400 p-4 mb-8 rounded-r-2xl shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0 mt-0.5">
                        <i class="fa-solid fa-triangle-exclamation text-amber-500 text-lg"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-[12px] text-amber-900 font-bold leading-relaxed uppercase tracking-tight">
                            Ingat! Anda hanya bisa memasukkan waktu pengerjaan satu kali, harap diperhatikan dengan baik.
                        </p>
                    </div>
                </div>
            </div>

            <form id="formSLA" method="POST">
                @csrf
                <div class="grid grid-cols-2 gap-5 mb-8">
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Angka</label>
                        <input type="number" name="estimasi_sla" placeholder="Contoh: 15"
                            class="bg-gray-50 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-0 outline-none transition-all" required>
                    </div>
                    <div class="flex flex-col gap-2">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest ml-2">Satuan</label>
                        <select name="satuan_sla" class="bg-gray-50 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 focus:bg-white focus:border-indigo-500 focus:ring-0 outline-none transition-all appearance-none cursor-pointer">
                            <option value="Menit">Menit</option>
                            <option value="Hari">Hari</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-indigo-600 text-white py-5 rounded-[1.5rem] font-black uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-1 transition-all active:scale-95">
                    Konfirmasi & Mulai
                </button>
            </form>
        </div>
    </div>

    {{-- MODAL UPLOAD FILE --}}
    <div id="modalUploadFile" class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-all">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-amber-50/50">
                <h3 class="text-lg font-black text-amber-800">Selesaikan & Unggah File</h3>
                <button type="button" onclick="tutupModalUpload()" class="text-gray-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="formUploadSelesai" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="mb-5 bg-amber-50 p-4 rounded-2xl border border-amber-100">
                    <p class="text-[10px] font-black text-amber-600 uppercase tracking-widest mb-1">Konfirmasi Selesai</p>
                    <p class="font-bold text-gray-800 text-sm" id="upload_nama_pengunjung"></p>
                </div>
                <div class="mb-6">
                    <label class="block text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-widest">File PDF</label>
                    <input type="file" name="file_surat" accept=".pdf" required class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm file:mr-4 file:py-1 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-black file:bg-amber-100 file:text-amber-700">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="tutupModalUpload()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-check-circle text-xs"></i> Simpan & Selesaikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EMAIL PIMPINAN --}}
    <div id="modalEmailPimpinan" class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-all">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-black text-gray-800">Teruskan ke Pimpinan</h3>
                <button type="button" onclick="tutupModalEmail()" class="text-gray-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form action="{{ route('kunjungan.kirim-email') }}" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="kunjungan_id" id="modal_kunjungan_id">
                <div class="mb-5 bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50">
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Informasi Kunjungan</p>
                    <p class="font-bold text-gray-800 text-sm" id="modal_nama_pengunjung"></p>
                    <p class="text-xs text-gray-500 mt-1 italic" id="modal_keperluan_pengunjung"></p>
                </div>
                <div class="mb-6">
                    <label class="block text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-widest">Email Pimpinan</label>
                    <div class="relative">
                        <i class="fa-solid fa-at absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                        <input type="email" name="email_pimpinan" id="email_pimpinan" required placeholder="pimpinan@poliban.ac.id" class="w-full pl-10 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="tutupModalEmail()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-paper-plane text-xs"></i> Kirim
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL TANGGAPAN PIMPINAN --}}
    <div id="modalTanggapanPimpinan" class="fixed inset-0 z-[100] hidden bg-gray-900/60 backdrop-blur-sm flex items-center justify-center transition-all">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden animate-modal-up">
            <div id="modalTanggapanHeader" class="px-6 py-5 border-b border-gray-100 flex justify-between items-center transition-colors duration-300">
                <h3 id="modalTanggapanTitle" class="text-lg font-black text-white">Tanggapan Pimpinan</h3>
                <button type="button" onclick="tutupModalTanggapan()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="formTanggapanPimpinan" method="POST" class="p-6">
                @csrf
                <input type="hidden" name="status_pimpinan" id="input_status_pimpinan">
                <div class="mb-5">
                    <label id="labelCatatan" class="block text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-widest"></label>
                    <textarea name="catatan_pimpinan" id="catatan_pimpinan" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-2xl text-sm focus:ring-2 outline-none h-32 resize-none transition-all"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="tutupModalTanggapan()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl">Batal</button>
                    <button type="submit" id="btnSubmitTanggapan" class="px-5 py-2.5 text-sm font-bold text-white rounded-xl shadow-lg transition-all">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes toast-in { from { transform: translateY(100px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-toast-in { animation: toast-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }
        @keyframes modal-up { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .animate-modal-up { animation: modal-up 0.3s ease-out forwards; }
    </style>

    <script>
        // Animasi Auto-Close Toast
        setTimeout(() => {
            const toasts = document.querySelectorAll('.toast-item');
            toasts.forEach(toast => {
                toast.style.transition = 'all 0.5s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);

        /**
         * FUNGSI MODAL ESTIMASI WAKTU (PROSES MULAI)
         */
     function bukaModalProsesSLA(nomorKunjungan) {
    const modal = document.getElementById('modalProsesSLA');
    const form = document.getElementById('formSLA');

    // Pastikan URL mengarah ke path yang benar di web.php
    // Kita kirim nomorKunjungan (misal: IN-260421-353)
    form.action = "{{ url('/dashboard/mulai-proses') }}/" + nomorKunjungan;

    modal.classList.remove('hidden');
}
function tutupModalSLA() {
        const modal = document.getElementById('modalProsesSLA');
        modal.classList.add('hidden');
        document.getElementById('formSLA').reset(); // Reset input saat ditutup
    }

    // FUNGSI GLOBAL: TUTUP MODAL SAAT KLIK DI AREA GELAP (OUTSIDE CLICK)
    window.onclick = function(event) {
        const modalSLA = document.getElementById('modalProsesSLA');
        const modalUpload = document.getElementById('modalUploadFile');
        const modalEmail = document.getElementById('modalEmailPimpinan');
        const modalTanggapan = document.getElementById('modalTanggapanPimpinan');

        if (event.target == modalSLA) tutupModalSLA();
        if (event.target == modalUpload) tutupModalUpload();
        if (event.target == modalEmail) tutupModalEmail();
        if (event.target == modalTanggapan) tutupModalTanggapan();
    }
        /**
         * FUNGSI MODAL UPLOAD SELESAI
         */
        function bukaModalUpload(id, nama) {
            document.getElementById('upload_nama_pengunjung').innerText = nama;
            document.getElementById('formUploadSelesai').action = `/dashboard/antrean/${id}/selesai`;
            document.getElementById('modalUploadFile').classList.remove('hidden');
        }

        function tutupModalUpload() {
            document.getElementById('modalUploadFile').classList.add('hidden');
        }

        /**
         * FUNGSI MODAL EMAIL PIMPINAN
         */
        function bukaModalEmail(id, nama, keperluan) {
            document.getElementById('modal_kunjungan_id').value = id;
            document.getElementById('modal_nama_pengunjung').innerText = nama;
            document.getElementById('modal_keperluan_pengunjung').innerText = keperluan ? `"${keperluan}"` : '-';
            document.getElementById('modalEmailPimpinan').classList.remove('hidden');
        }

        function tutupModalEmail() {
            document.getElementById('modalEmailPimpinan').classList.add('hidden');
        }

        /**
         * FUNGSI MODAL TANGGAPAN PIMPINAN
         */
        function bukaModalTanggapan(id, status) {
            const modal = document.getElementById('modalTanggapanPimpinan');
            const form = document.getElementById('formTanggapanPimpinan');
            const header = document.getElementById('modalTanggapanHeader');
            const title = document.getElementById('modalTanggapanTitle');
            const label = document.getElementById('labelCatatan');
            const btn = document.getElementById('btnSubmitTanggapan');
            const inputStatus = document.getElementById('input_status_pimpinan');

            form.action = `/dashboard/antrean/${id}/tanggapan`;
            inputStatus.value = status;

            if (status === 'Disetujui') {
                title.innerText = 'Terima Kunjungan';
                header.className = 'px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-emerald-500';
                label.innerText = 'Instruksi untuk Pengunjung';
                btn.className = 'px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-lg';
            } else {
                title.innerText = 'Tolak Kunjungan';
                header.className = 'px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-rose-500';
                label.innerText = 'Alasan Penolakan';
                btn.className = 'px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl shadow-lg';
            }
            modal.classList.remove('hidden');
        }

        function tutupModalTanggapan() {
            document.getElementById('modalTanggapanPimpinan').classList.add('hidden');
        }
    </script>
@endsection
