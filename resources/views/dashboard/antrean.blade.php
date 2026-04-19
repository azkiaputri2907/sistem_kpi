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

                    {{-- REVISI: MENAMPILKAN DETAIL KEPERLUAN DI KOLOM NAMA --}}
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
                        @if($k->status_sla == 'Tepat Waktu')
                            <span class="text-emerald-500 font-black text-[10px] flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-check"></i> TEPAT WAKTU
                            </span>
                        @elseif($k->status_sla == 'Terlambat')
                            <span class="text-rose-500 font-black text-[10px] flex items-center justify-center gap-1">
                                <i class="fa-solid fa-circle-exclamation"></i> TERLAMBAT
                            </span>
                        @else
                            <span class="text-gray-300 text-[10px] italic">-</span>
                        @endif
                    </td>

                    <td class="px-8 py-6 text-center">
                        @php
                            $pimpinanColor = match($k->status_pimpinan) {
                                'Disetujui' => 'bg-emerald-100 text-emerald-600',
                                'Ditolak' => 'bg-rose-100 text-rose-600',
                                default => 'bg-amber-100 text-amber-600'
                            };
                        @endphp
                        <span class="px-4 py-1.5 {{ $pimpinanColor }} rounded-full text-[9px] font-black uppercase tracking-widest inline-block">
                            {{ $k->status_pimpinan }}
                        </span>
                        @if($k->catatan_pimpinan)
                            <p class="text-[10px] text-gray-500 font-medium italic mt-1 max-w-[150px] mx-auto leading-tight" title="{{ $k->catatan_pimpinan }}">
                                "{{ $k->catatan_pimpinan }}"
                            </p>
                        @endif
                    </td>

                    <td class="px-8 py-6 text-center">
                        <div class="flex justify-center gap-2">
                            @if(!in_array(Auth::user()->role_id, [1, 2]))
                                @if($k->status_pimpinan == 'Menunggu')
                                    <button type="button" onclick="bukaModalTanggapan('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}')"
                                            class="h-9 px-4 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm text-xs font-bold">
                                        Beri Tanggapan
                                    </button>
                                @else
                                    <span class="text-[10px] font-bold text-gray-400 italic bg-gray-50 px-3 py-1.5 rounded-lg">Sudah ditanggapi</span>
                                @endif
                            @else
                                <a href="{{ url('/status/'.$k->nomor_kunjungan) }}" target="_blank" class="w-9 h-9 flex items-center justify-center bg-gray-50 text-gray-400 rounded-xl hover:bg-slate-800 hover:text-white transition-all shadow-sm">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </a>

                                <button type="button" onclick="bukaModalEmail('{{ $k->id }}', '{{ $k->pengunjung->nama_lengkap ?? 'Umum' }}', '{{ addslashes($k->keperluan) }}')"
                                        class="w-9 h-9 flex items-center justify-center bg-blue-50 text-blue-500 rounded-xl hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Teruskan ke Pimpinan">
                                    <i class="fa-solid fa-envelope text-xs"></i>
                                </button>

                                @if($k->status_layanan == 'Antre')
                                    <form action="{{ route('kunjungan.mulaiProses', $k->nomor_kunjungan) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Mulai Layanan">
                                            <i class="fa-solid fa-play text-xs ml-0.5"></i>
                                        </button>
                                    </form>
                                @elseif($k->status_layanan == 'Diproses')
                                    <form action="{{ route('kunjungan.selesai', $k->nomor_kunjungan) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="w-9 h-9 flex items-center justify-center bg-emerald-50 text-emerald-600 rounded-xl hover:bg-emerald-500 hover:text-white transition-all shadow-sm" title="Selesai">
                                            <i class="fa-solid fa-check text-xs"></i>
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-8 py-20 text-center text-gray-400">Data tidak ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- MODAL EMAIL --}}
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
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-black text-gray-800">Tanggapan Pimpinan</h3>
                <button type="button" onclick="tutupModalTanggapan()" class="text-gray-400 hover:text-rose-500 transition-colors">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <form id="formTanggapan" method="POST" class="p-6">
                @csrf
                <div class="mb-5 bg-indigo-50/50 p-4 rounded-2xl border border-indigo-100/50">
                    <p class="text-[10px] font-black text-indigo-500 uppercase tracking-widest mb-1">Menanggapi Kunjungan</p>
                    <p class="font-bold text-gray-800 text-sm" id="tanggapan_nama_pengunjung"></p>
                </div>
                <div class="mb-4">
                    <label class="block text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-widest">Keputusan</label>
                    <select name="status_pimpinan" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" required>
                        <option value="Disetujui">Setujui (Bisa Ditemui)</option>
                        <option value="Ditolak">Tolak (Sedang Sibuk / Tidak di Tempat)</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-[11px] font-bold text-gray-500 mb-2 uppercase tracking-widest">Catatan Untuk Admin (Opsional)</label>
                    <textarea name="catatan_pimpinan" rows="3" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 outline-none" placeholder="Misal: Suruh tunggu di ruang tamu..."></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="tutupModalTanggapan()" class="px-5 py-2.5 text-sm font-bold text-gray-600 bg-gray-100 rounded-xl">Batal</button>
                    <button type="submit" class="px-5 py-2.5 text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-lg flex items-center gap-2">
                        <i class="fa-solid fa-check text-xs"></i> Simpan Tanggapan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes toast-in {
            from { transform: translateY(100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-toast-in { animation: toast-in 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards; }

        @keyframes modal-up {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .animate-modal-up { animation: modal-up 0.3s ease-out forwards; }
    </style>

    <script>
        setTimeout(() => {
            const toasts = document.querySelectorAll('.toast-item');
            toasts.forEach(toast => {
                toast.style.transition = 'all 0.5s ease';
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(100px)';
                setTimeout(() => toast.remove(), 500);
            });
        }, 5000);

        function bukaModalEmail(id, nama, keperluan) {
            document.getElementById('modal_kunjungan_id').value = id;
            document.getElementById('modal_nama_pengunjung').innerText = nama;
            document.getElementById('modal_keperluan_pengunjung').innerText = keperluan ? `"${keperluan}"` : '-';
            document.getElementById('modalEmailPimpinan').classList.remove('hidden');
        }
        function tutupModalEmail() { document.getElementById('modalEmailPimpinan').classList.add('hidden'); }

        function bukaModalTanggapan(id, nama) {
            document.getElementById('tanggapan_nama_pengunjung').innerText = nama;
            document.getElementById('formTanggapan').action = `/dashboard/antrean/${id}/tanggapan`;
            document.getElementById('modalTanggapanPimpinan').classList.remove('hidden');
        }
        function tutupModalTanggapan() { document.getElementById('modalTanggapanPimpinan').classList.add('hidden'); }
    </script>
@endsection
