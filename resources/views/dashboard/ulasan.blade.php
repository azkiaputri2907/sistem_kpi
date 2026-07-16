@extends('layouts.app')
<style>
    .swal2-backdrop-show {
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
}
</style>

@section('title', 'Ulasan Layanan')

@section('content')

@php
    $isSuper = $user->role_id == 1 || $user->role_id == 3;
@endphp

<div class="px-4 sm:px-8 py-6 max-w-7xl mx-auto text-slate-800 dark:text-slate-100 transition-colors duration-300">

    {{-- HEADER & UTILITY SECTION --}}
    <div class="mb-12 flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-slate-700/50">
        <div>
            <h1 class="text-3xl sm:text-4xl font-black text-slate-800 dark:text-white tracking-tight text-left">
                Ulasan Layanan
            </h1>
            <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium italic text-left text-sm">
                Pantau umpan balik pengunjung secara waktu nyata.
            </p>
        </div>

<div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">

            {{-- FILTER PRODI --}}
            <form action="{{ route('dashboard.ulasan') }}" method="GET" class="w-full sm:w-auto m-0 flex items-center">
                <div class="relative w-full sm:w-auto">
                    <select name="prodi_id"
                        onchange="this.form.submit()"
                        {{ !$isSuper ? 'disabled' : '' }}
                        {{-- PERBAIKAN: py-3 agar sama tinggi dengan tombol laporan ulasan --}}
                        class="w-full sm:w-[280px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-950/50 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-100 dark:bg-slate-900 cursor-not-allowed text-slate-400 dark:text-slate-500' : '' }}">

                        @if($isSuper)
                            <option value="" class="dark:bg-slate-800">🌍 Seluruh Program Studi</option>
                            @foreach($daftar_prodi as $p)
                                <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                                    🎓 {{ $p->nama }}
                                </option>
                            @endforeach
                        @else
                            <option value="{{ $user->prodi_id }}" selected class="dark:bg-slate-800">
                                🎓 {{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}
                            </option>
                        @endif
                    </select>

                    <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500 text-xs">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </form>
            
            {{-- TOMBOL TRIGGER MODAL EKSPOR PREMIUM --}}
            <button type="button" onclick="openExportModal('ulasan')"
                class="inline-flex justify-center items-center w-full sm:w-auto bg-gradient-to-r from-slate-900 via-blue-900 to-red-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all duration-300 shadow-blue-900/30">
                <i class="fa-solid fa-file-export mr-2"></i>
                Laporan Ulasan
            </button>

        </div>
    </div>

    {{-- GRID ULASAN KARTU --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($data_ulasan as $item)
            @php
                $detail = $item->survey->detail;
                $avgRating = ($detail->p1 + $detail->p2 + $detail->p3 + $detail->p4 + $detail->p5) / 5;
                $ratingBulat = round($avgRating);
            @endphp

            <div class="bg-white dark:bg-slate-800 p-6 sm:p-10 rounded-[2rem] sm:rounded-[3rem] border border-gray-50 dark:border-slate-700/60 shadow-sm hover:shadow-xl dark:hover:shadow-slate-950/30 hover:-translate-y-1 transition-all duration-300 flex flex-col h-full group">

                <div class="flex justify-between items-start mb-8">
                    <div class="flex gap-1 text-amber-400">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fa-solid fa-star text-sm {{ $i <= $ratingBulat ? '' : 'text-gray-200 dark:text-slate-700' }}"></i>
                        @endfor
                    </div>

                    {{-- ASAL INSTANSI DIUBAH JADI DIRAHASIAKAN --}}
                    <span class="bg-slate-50 dark:bg-slate-700 text-slate-400 dark:text-slate-300 text-[9px] font-black px-4 py-1.5 rounded-full uppercase tracking-widest border border-slate-100 dark:border-slate-600 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/50 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 group-hover:border-indigo-100 dark:group-hover:border-indigo-900 transition-colors">
                        Dirahasiakan
                    </span>
                </div>

                <div class="flex-grow">
                    <p class="text-slate-800 dark:text-slate-200 font-bold text-lg sm:text-xl leading-relaxed mb-10 text-left">
                        "{!! e($item->survey->kritik_saran ?? 'Hanya memberikan rating bintang.') !!}"
                    </p>
                </div>

                <div class="mt-auto pt-6 border-t border-gray-50 dark:border-slate-700/50 flex flex-col text-left">
                    {{-- NAMA PENGUNJUNG DIUBAH JADI ANONIM --}}
                    <span class="text-slate-900 dark:text-white font-black text-base">
                        Anonim
                    </span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 dark:bg-indigo-500"></span>
                        <span class="text-slate-400 dark:text-slate-500 text-[11px] font-bold uppercase tracking-wider">
                            {{ $item->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>

            </div>
        @endforeach
    </div>

    {{-- EMPTY DATA TEMPLATE --}}
    @if($data_ulasan->isEmpty())
        <div class="py-20 text-center">
            <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300 dark:text-slate-600 transition-colors">
                <i class="fa-solid fa-comment-slash text-2xl"></i>
            </div>
            <p class="text-gray-400 dark:text-slate-500 font-bold">
                Belum ada ulasan yang masuk.
            </p>
        </div>
    @endif

</div>

{{-- MODAL EKSPOR PERIODE --}}
<div id="exportModal" class="fixed inset-0 z-[999] hidden bg-slate-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div id="modalContentExport" class="bg-white dark:bg-slate-800 rounded-[1.5rem] md:rounded-[2.5rem] p-6 md:p-10 max-w-md w-full shadow-2xl opacity-0 scale-95 transform transition-all duration-200 border border-transparent dark:border-slate-700">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl md:text-2xl font-black text-slate-800 dark:text-white tracking-tight">Periode Laporan</h3>
                <p class="text-slate-400 dark:text-slate-500 text-xs font-medium mt-1">Tentukan tanggal penarikan data laporan</p>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-gray-50 dark:bg-slate-700 text-gray-400 dark:text-gray-300 hover:bg-rose-50 dark:hover:bg-rose-900/50 hover:text-rose-500 dark:hover:text-rose-400 transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="mb-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/40">
            <p class="text-[10px] uppercase font-black tracking-widest text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-circle-info mr-1"></i> Informasi Ekspor
            </p>
            <p class="text-xs text-indigo-900 dark:text-indigo-300 font-semibold leading-relaxed">
                Data laporan yang diunduh akan disesuaikan otomatis berdasarkan rentang tanggal dan filter aktif prodi Anda.
            </p>
        </div>

        <div class="space-y-5 mb-8">
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase ml-2 tracking-widest">Tanggal Awal</label>
                <input type="date" id="exportStartDate" required
                    class="w-full bg-gray-50 dark:bg-slate-700 border-2 border-transparent rounded-2xl p-4 font-bold text-slate-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase ml-2 tracking-widest">Tanggal Akhir</label>
                <input type="date" id="exportEndDate" required
                    class="w-full bg-gray-50 dark:bg-slate-700 border-2 border-transparent rounded-2xl p-4 font-bold text-slate-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <button id="btnExcel" class="flex items-center justify-center gap-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
                <i class="fa-regular fa-file-excel text-base"></i> Excel
            </button>
            <button id="btnPdf" class="flex items-center justify-center gap-2.5 bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
                <i class="fa-regular fa-file-pdf text-base"></i> PDF
            </button>
        </div>
    </div>
</div>

{{-- MODAL LOADING POP-UP --}}
<div id="loading-modal" class="fixed inset-0 z-[1000] hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-white dark:bg-slate-800 p-6 md:p-8 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-slate-700 flex flex-col items-center max-w-xs w-full text-center">
        <div class="w-12 h-12 border-4 border-indigo-600 dark:border-indigo-400 border-t-transparent dark:border-t-transparent rounded-full animate-spin mb-4"></div>
        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider mb-1">Menyiapkan Dokumen</h3>
        <p class="text-[11px] text-slate-400 dark:text-slate-400 leading-relaxed">Mohon tunggu, sistem sedang memproses data laporan Anda...</p>
    </div>
</div>

@endsection

@push('scripts')
<script>
let exportRoute = 'ulasan';
let isModalOpen = false;

function openExportModal(laporan){
    if(laporan) exportRoute = laporan;
    isModalOpen = true;

    document.getElementById('exportStartDate').value = '';
    document.getElementById('exportEndDate').value = '';

    const modal = document.getElementById('exportModal');
    const content = document.getElementById('modalContentExport');

    modal.classList.remove('hidden');
    modal.classList.add('flex');

    setTimeout(() => {
        if (content) {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }
    }, 10);
}

function closeExportModal(){
    const modal = document.getElementById('exportModal');
    const content = document.getElementById('modalContentExport');

    if (content) {
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
    }

    setTimeout(() => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        isModalOpen = false;
    }, 200);
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('btnExcel').addEventListener('click', function(){
        downloadLaporan('xlsx');
    });

    document.getElementById('btnPdf').addEventListener('click', function(){
        downloadLaporan('pdf');
    });
});

function downloadLaporan(type){
    const startDate = document.getElementById('exportStartDate').value;
    const endDate = document.getElementById('exportEndDate').value;

    if(!startDate || !endDate){
        alert('Silakan pilih rentang tanggal terlebih dahulu.');
        return;
    }

    const loadingModal = document.getElementById('loading-modal');
    if (loadingModal) {
        loadingModal.classList.remove('hidden');
    }

    closeExportModal();
    isModalOpen = true;

    const prodiSelect = document.querySelector('[name=prodi_id]');
    const prodi = prodiSelect ? prodiSelect.value : '';

    window.location = '/laporan/' + exportRoute +
                      '?type=' + type +
                      '&start_date=' + startDate +
                      '&end_date=' + endDate +
                      '&prodi_id=' + encodeURIComponent(prodi);

    setTimeout(function() {
        if (loadingModal) {
            loadingModal.classList.add('hidden');
        }
        isModalOpen = false;
    }, 15000);
}
</script>
@endpush
