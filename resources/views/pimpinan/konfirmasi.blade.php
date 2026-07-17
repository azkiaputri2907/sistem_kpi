@extends('layouts.app')
<style>
    .swal2-backdrop-show {
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
}
</style>

@section('title', 'Konfirmasi Pimpinan')

@section('content')
<div class="mb-10 px-4 sm:px-0">
    <h2 class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-slate-100 tracking-tight transition-colors duration-200">Konfirmasi Layanan</h2>
    <p class="text-slate-400 dark:text-slate-500 text-sm font-medium mt-3 transition-colors duration-200">Daftar permintaan persetujuan atau tanggapan pimpinan yang perlu diproses.</p>
</div>

@php $count = 0; @endphp

{{-- =========================================================================
    1. TAMPILAN KHUSUS LAYAR KECIL / HP (CARD STACK)
========================================================================= --}}
<div class="block md:hidden space-y-4 px-4">
    @foreach($data_konfirmasi as $item)
        @if($item->is_forwarded == 1 || $item->is_forwarded == true)
        @php $count++; @endphp
        <div class="bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 p-6 rounded-[2rem] shadow-sm space-y-4 transition-all duration-200">
            
            <div class="flex items-center justify-between border-b border-slate-50 dark:border-slate-800/60 pb-3">
                <div>
                    <span class="text-sm font-black text-slate-800 dark:text-slate-200">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                    </span>
                    <span class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase ml-2">
                        {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                    </span>
                </div>
                <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg text-[9px] font-black uppercase tracking-widest">
                    Antrean: {{ $item->nomor_kunjungan }}
                </span>
            </div>

            <div>
                <p class="text-xs text-slate-400 dark:text-slate-500 font-black uppercase tracking-wider mb-0.5">Pengunjung</p>
                <p class="font-extrabold text-slate-800 dark:text-slate-200 text-base">
                    {{ $item->pengunjung->nama_lengkap ?? 'Umum' }}
                </p>
                
                {{-- INFO INSTANSI DAN TIPE TAMU --}}
                <div class="flex items-center gap-2 mt-1 mb-2">
                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ strtolower($item->tipe_tamu ?? $item->pengunjung->tipe_tamu ?? '') == 'internal' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400' }}">
                        {{ $item->tipe_tamu ?? $item->pengunjung->tipe_tamu ?? 'Eksternal' }}
                    </span>
                    <p class="text-[11px] font-bold text-indigo-500 dark:text-indigo-400 uppercase truncate">
                        {{ $item->pengunjung->asal_instansi ?? '-' }}
                    </p>
                </div>
                
                <div class="flex items-center gap-2">

                    @if(!empty($item->pengunjung->no_telepon))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->pengunjung->no_telepon) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded text-[9px] font-bold transition-colors">
                            <i class="fa-brands fa-whatsapp text-sm"></i> {{ $item->pengunjung->no_telepon }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/40 p-4 rounded-xl">
                <p class="text-[10px] text-slate-400 dark:text-slate-500 font-black uppercase tracking-wider mb-1">Keperluan</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $item->nama_keperluan_utama }}</p>
                @if(!empty($item->keperluan) && $item->keperluan !== '-')
                    <p class="text-xs text-slate-600 dark:text-slate-400 italic font-medium mt-1">Detail: "{{ $item->keperluan }}"</p>
                @endif
                
                {{-- TOMBOL SURAT DISPOSISI (MOBILE) --}}
                <div class="mt-3 pt-3 border-t border-slate-200 dark:border-slate-700/50">
                    @if(!empty($item->surat_disposisi) && $item->surat_disposisi !== '-')
                        <a href="{{ filter_var($item->surat_disposisi, FILTER_VALIDATE_URL) ? $item->surat_disposisi : asset($item->surat_disposisi) }}" target="_blank" 
                           class="inline-flex items-center justify-center w-full gap-2 px-3 py-2 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/50 dark:hover:bg-blue-800/50 text-blue-700 dark:text-blue-300 rounded-lg text-[10px] font-bold transition-colors border border-blue-200 dark:border-blue-800">
                            <i class="fa-solid fa-file-arrow-down text-sm"></i> Lihat Surat Disposisi
                        </a>
                    @else
                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic"><i class="fa-solid fa-file-circle-xmark mr-1"></i> Tidak ada surat lampiran</p>
                    @endif
                </div>
            </div>

            <div class="pt-2">
                @if(!$item->status_pimpinan || str_contains(strtolower($item->status_pimpinan), 'menunggu'))
                    <button
                        onclick="bukaModalTanggapan('{{ $item->id }}', '{{ $item->pengunjung->nama_lengkap ?? 'Umum' }}', '{{ $item->nomor_kunjungan }}', '{{ htmlspecialchars($item->nama_keperluan_utama, ENT_QUOTES) }}', '{{ htmlspecialchars($item->keperluan ?? '-', ENT_QUOTES) }}')"
                        class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white text-xs font-black uppercase tracking-widest rounded-xl transition-all duration-150 active:scale-95 text-center shadow-lg dark:shadow-none">
                        Beri Tanggapan
                    </button>
                @else
                    <div class="w-full text-center py-2.5 rounded-xl text-[10px] font-black uppercase tracking-wider
                        {{ $item->status_pimpinan == 'Setuju'
                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400'
                            : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400'
                        }}">
                        Selesai: {{ $item->status_pimpinan }}
                    </div>
                @endif
            </div>

        </div>
        @endif
    @endforeach
</div>

{{-- =========================================================================
    2. TAMPILAN KHUSUS LAYAR LEBAR / DESKTOP (TABEL KONVENSIONAL)
========================================================================= --}}
<div class="hidden md:block mx-4 sm:mx-0 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 shadow-sm overflow-hidden transition-all duration-200">
    <div class="overflow-x-auto custom-scrollbar">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/50 dark:bg-slate-800/40 border-b border-slate-100 dark:border-slate-800">
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">No. Antrean</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Nama Pengunjung</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest whitespace-nowrap">Waktu Masuk</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest min-w-[200px]">Keperluan (Detail & Surat)</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Kontak WA</th>
                    <th class="px-6 py-5 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50 dark:divide-slate-800/60">
                @foreach($data_konfirmasi as $item)
                    @if($item->is_forwarded == 1 || $item->is_forwarded == true)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors duration-150">
                        <td class="px-6 py-6 text-center">
                            <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg text-[10px] font-black uppercase tracking-widest">
                                {{ $item->nomor_kunjungan }}
                            </span>
                        </td>
                        <td class="px-6 py-6">
                            <p class="font-extrabold text-slate-800 dark:text-slate-200 text-sm">
                                {{ $item->pengunjung->nama_lengkap ?? 'Umum' }}
                            </p>
                            
                            {{-- INFO INSTANSI DAN TIPE TAMU --}}
                            <div class="flex items-center gap-2 mt-1.5">
                                <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ strtolower($item->tipe_tamu ?? $item->pengunjung->tipe_tamu ?? '') == 'internal' ? 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-400' : 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400' }}">
                                    {{ $item->tipe_tamu ?? $item->pengunjung->tipe_tamu ?? 'Eksternal' }}
                                </span>
                                <p class="text-[10px] font-bold text-indigo-500 dark:text-indigo-400 uppercase truncate">
                                    {{ $item->pengunjung->asal_instansi ?? '-' }}
                                </p>
                            </div>
                        </td>
                        <td class="px-6 py-6 whitespace-nowrap">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('H:i') }}
                            </p>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-bold uppercase mt-1">
                                {{ \Carbon\Carbon::parse($item->created_at)->format('d M Y') }}
                            </p>
                        </td>
                        <td class="px-6 py-6">
                            <p class="text-sm font-bold text-slate-800 dark:text-slate-200">{{ $item->nama_keperluan_utama }}</p>
                            @if(!empty($item->keperluan) && $item->keperluan !== '-')
                                <p class="text-xs text-slate-600 dark:text-slate-400 italic font-medium leading-relaxed mt-1.5">Detail: "{{ $item->keperluan }}"</p>
                            @endif
                            
                            {{-- TOMBOL SURAT DISPOSISI (DESKTOP) --}}
                            @if(!empty($item->surat_disposisi) && $item->surat_disposisi !== '-')
                                <div class="mt-2.5">
                                    <a href="{{ filter_var($item->surat_disposisi, FILTER_VALIDATE_URL) ? $item->surat_disposisi : asset($item->surat_disposisi) }}" target="_blank" 
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/30 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg text-[10px] font-bold transition-colors border border-blue-100 dark:border-blue-800/60 shadow-sm">
                                        <i class="fa-solid fa-file-arrow-down text-sm"></i> Surat Disposisi
                                    </a>
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-6">
                            @if(!empty($item->pengunjung->no_telepon))
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $item->pengunjung->no_telepon) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/30 dark:hover:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 rounded-lg text-[10px] font-bold transition-colors">
                                    <i class="fa-brands fa-whatsapp text-sm"></i> {{ $item->pengunjung->no_telepon }}
                                </a>
                            @else
                                <span class="text-xs text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-6 text-center">
                            @if(!$item->status_pimpinan || str_contains(strtolower($item->status_pimpinan), 'menunggu'))
                                <button
                                    onclick="bukaModalTanggapan('{{ $item->id }}', '{{ $item->pengunjung->nama_lengkap ?? 'Umum' }}', '{{ $item->nomor_kunjungan }}', '{{ htmlspecialchars($item->nama_keperluan_utama, ENT_QUOTES) }}', '{{ htmlspecialchars($item->keperluan ?? '-', ENT_QUOTES) }}')"
                                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-500 dark:hover:bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all duration-150 transform active:scale-95 whitespace-nowrap shadow-md dark:shadow-none">
                                    Beri Tanggapan
                                </button>
                            @else
                                <div class="flex flex-col items-center gap-2">
                                    <span class="px-4 py-1.5 rounded-lg text-[10px] font-black uppercase tracking-wider
                                        {{ $item->status_pimpinan == 'Setuju'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400'
                                            : 'bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400'
                                        }}">
                                        {{ $item->status_pimpinan }}
                                    </span>
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- TAMPILKAN PESAN KOSONG GLOBAL --}}
@if($count == 0)
<div class="mx-4 sm:mx-0 bg-white dark:bg-slate-900 rounded-[2.5rem] border border-slate-100 dark:border-slate-800 p-20 text-center transition-all duration-200">
    <div class="flex flex-col items-center opacity-30 dark:opacity-40 text-slate-400 dark:text-slate-500">
        <i class="fa-solid fa-inbox text-5xl mb-4"></i>
        <p class="font-bold uppercase tracking-widest text-sm">Belum ada data diteruskan</p>
    </div>
</div>
@endif

{{-- =========================================================================
    3. MODAL TANGGAPAN (GAYA BARU MENGIKUTI CONTOH KODE ANDA)
========================================================================= --}}
<div id="modalTanggapan" class="fixed inset-0 z-[120] hidden bg-black/40 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 w-full max-w-md rounded-[2rem] shadow-2xl overflow-hidden animate-modal-up transition-colors duration-300">
        
        {{-- Header Modal --}}
        <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-gray-800 dark:text-white">Konfirmasi Layanan</h3>
                <p class="text-xs text-gray-400 dark:text-gray-400 mt-1">Pilih keputusan disposisi layanan</p>
            </div>
            <button type="button" onclick="tutupModal()" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-rose-100 dark:hover:bg-rose-900/50 text-gray-400 dark:text-gray-300 hover:text-rose-500 dark:hover:text-rose-400 transition-all">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        {{-- Form di dalam Modal --}}
        <form id="formTanggapan" method="POST" onsubmit="tampilkanLoading()" class="p-6">
            @csrf
            
            {{-- Kotak Detail Info Pengunjung & Keperluan --}}
            <div class="mb-6 space-y-3">
                <div class="bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 rounded-2xl p-4 flex justify-between items-center">
                    <div>
                        <p class="text-[10px] font-black uppercase tracking-widest text-indigo-500 dark:text-indigo-400 mb-0.5">Pengunjung</p>
                        <p id="modal_nama_pengunjung" class="font-bold text-gray-800 dark:text-gray-200 text-sm">-</p>
                    </div>
                    <span id="modal_nomor_antrean" class="px-3 py-1 bg-indigo-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-sm">
                        -
                    </span>
                </div>

                {{-- Kotak Baru Khusus Keperluan --}}
                <div class="bg-slate-50 dark:bg-slate-800/50 border border-slate-100 dark:border-slate-700 rounded-2xl p-4">
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400 mb-1">Keperluan Layanan</p>
                    <p id="modal_keperluan_utama" class="font-bold text-gray-800 dark:text-gray-200 text-sm">-</p>
                    <p id="modal_keperluan_detail" class="text-xs text-slate-700 dark:text-slate-300 italic font-medium leading-relaxed mt-1.5 hidden"></p>
                </div>
            </div>

            {{-- Radio Pilihan Status (Setuju / Tolak) Ber-Ikon --}}
            <div class="space-y-3 mb-6">
                <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/10 transition-all cursor-pointer group">
                    <input type="radio" name="status_pimpinan" value="Setuju" required checked class="w-5 h-5 text-emerald-600 dark:text-emerald-400 focus:ring-emerald-500">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg transition-transform group-hover:scale-105">
                            <i class="fa-solid fa-circle-check"></i>
                        </div>
                        <div>
                            <p class="font-black text-gray-800 dark:text-gray-200 text-sm">Setujui Layanan</p>
                            <p class="text-xs text-gray-400 dark:text-gray-400">Izinkan permintaan pengunjung</p>
                        </div>
                    </div>
                </label>

                <label class="flex items-center gap-4 p-4 rounded-2xl border border-gray-200 dark:border-gray-700 hover:border-rose-500 dark:hover:border-rose-400 hover:bg-rose-50/50 dark:hover:bg-rose-950/10 transition-all cursor-pointer group">
                    <input type="radio" name="status_pimpinan" value="Ditolak" required class="w-5 h-5 text-rose-600 dark:text-rose-400 focus:ring-rose-500">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/50 text-rose-600 dark:text-rose-400 flex items-center justify-center text-lg transition-transform group-hover:scale-105">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>
                        <div>
                            <p class="font-black text-gray-800 dark:text-gray-200 text-sm">Tolak / Tangguhkan</p>
                            <p class="text-xs text-gray-400 dark:text-gray-400">Tolak permintaan kunjungan</p>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Input Pesan / Instruksi Tambahan --}}
            <div class="mb-8">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-400 uppercase tracking-widest block mb-2">Pesan / Instruksi Tambahan</label>
                <textarea name="catatan_pimpinan" rows="3" required 
                    class="w-full bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-2xl p-4 text-sm font-medium text-gray-800 dark:text-gray-100 focus:bg-white dark:focus:bg-gray-700 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all shadow-inner placeholder-gray-400 dark:placeholder-gray-500" 
                    placeholder="Contoh: Silakan temui saya di ruangan..."></textarea>
            </div>
            
            {{-- Tombol Aksi Horizontal --}}
            <div class="flex gap-3">
                <button type="button" onclick="tutupModal()" class="flex-1 py-3 rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-bold text-sm hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">Batal</button>
                <button type="submit" class="flex-1 py-3 rounded-2xl bg-indigo-600 dark:bg-indigo-500 hover:bg-indigo-700 dark:hover:bg-indigo-600 text-white font-black text-sm shadow-lg dark:shadow-none transition-all">Kirim</button>
            </div>
        </form>
    </div>
</div>

{{-- =========================================================================
    4. POP-UP OVERLAY LOADING
========================================================================= --}}
<div id="loadingOverlay" class="fixed inset-0 z-[200] hidden bg-slate-900/70 dark:bg-slate-950/85 backdrop-blur-md flex-col items-center justify-center p-4 transition-all duration-300">
    <div class="bg-white dark:bg-slate-900 p-8 rounded-[2rem] shadow-2xl flex flex-col items-center text-center max-w-xs w-full border border-transparent dark:border-slate-800">
        <div class="relative w-16 h-16 mb-5">
            <div class="absolute inset-0 border-4 border-slate-100 dark:border-slate-800 rounded-full"></div>
            <div class="absolute inset-0 border-4 border-indigo-600 dark:border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
        </div>
        <h4 class="text-lg font-black text-slate-800 dark:text-slate-100 tracking-tight">Memproses Keputusan</h4>
        <p class="text-xs font-semibold text-slate-400 dark:text-slate-500 mt-2">Menyimpan data perubahan ke Google Sheets, mohon tunggu...</p>
    </div>
</div>

<script>
    function bukaModalTanggapan(id, nama, antrean, keperluanUtama, keperluanDetail) {
        const modal = document.getElementById('modalTanggapan');
        const form = document.getElementById('formTanggapan');
        
        // Pasang data dinamis ke elemen modal
        document.getElementById('modal_nama_pengunjung').innerText = nama;
        document.getElementById('modal_nomor_antrean').innerText = 'Antrean: ' + antrean;
        
        // Memasukkan detail keperluan utama ke dalam modal
        document.getElementById('modal_keperluan_utama').innerText = keperluanUtama;
        
        // Cek jika ada detail, maka tampilkan. Jika '-', maka sembunyikan.
        const detailEl = document.getElementById('modal_keperluan_detail');
        if (keperluanDetail && keperluanDetail !== '-') {
            detailEl.innerText = 'Detail: "' + keperluanDetail + '"';
            detailEl.classList.remove('hidden');
        } else {
            detailEl.classList.add('hidden');
            detailEl.innerText = '';
        }
        
        form.action = `/dashboard/pimpinan/konfirmasi/${id}/tanggapan`;
        form.reset(); // Mengembalikan pilihan form ke kondisi awal (default Setuju)
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    function tutupModal() {
        const modal = document.getElementById('modalTanggapan');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function tampilkanLoading() {
        // Menyembunyikan modal tanggapan utama
        document.getElementById('modalTanggapan').classList.add('hidden');
        
        // Memunculkan Pop-up Loading Full Screen
        const loading = document.getElementById('loadingOverlay');
        loading.classList.remove('hidden');
        loading.classList.add('flex');
    }
</script>
@endsection