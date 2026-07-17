@extends('layouts.app')

<style>
    .swal2-backdrop-show {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
    }
</style>

@section('content')
<div class="px-4 sm:px-8 py-6 max-w-7xl mx-auto text-slate-800 dark:text-slate-100 transition-colors duration-300">

    {{-- HEADER & UTILITY SECTION --}}
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-8 pb-6 border-b border-slate-100 dark:border-slate-700/50">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-1">Laporan & Ekspor</h1>
            <p class="text-slate-500 dark:text-slate-400 text-sm">Unduh rekapitulasi performa dan analisis data layanan.</p>
        </div>

        {{-- ACTIONS ROW --}}
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">

            {{-- FORM FILTER PRODI --}}
            <form action="{{ route('dashboard.laporan') }}" method="GET" class="w-full sm:w-auto m-0">
                @php
                    $isSuper = $user->role_id == 1 || $user->role_id == 3;
                @endphp

                <div class="relative w-full sm:w-64">
                    <select name="prodi_id"
                        onchange="handleSelectProdiLoading(this)"
                        {{ !$isSuper ? 'disabled' : '' }}
                        class="w-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl pl-4 pr-10 py-3 text-sm font-bold text-slate-700 dark:text-slate-200 focus:border-indigo-500 dark:focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-50 dark:bg-slate-900 cursor-not-allowed text-slate-400 dark:text-slate-500 border-slate-200 dark:border-slate-800' : '' }}">

                        @if($isSuper)
                            <option value="" class="dark:bg-slate-800">Seluruh Program Studi</option>
                            @foreach($daftar_prodi as $p)
                                <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                                     {{ $p->nama }}
                                </option>
                            @endforeach
                        @else
                            <option selected class="dark:bg-slate-800">
                                 {{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}
                            </option>
                        @endif
                    </select>
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 dark:text-slate-500 text-xs">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </form>

            {{-- DROPDOWN EKSPOR DENGAN DESAIN GRADASI PREMIUM --}}
            <div class="relative w-full sm:w-auto text-left">
                <button type="button" onclick="toggleExportDropdown()" id="btnDropdownTrigger"
                    class="inline-flex justify-center items-center gap-2 w-full sm:w-auto bg-gradient-to-r from-slate-900 via-blue-900 to-red-600 text-white px-6 py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all duration-300 shadow-blue-900/30">
                    <i class="fa-solid fa-file-export text-base"></i>
                    <span>Ekspor Laporan</span>
                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200 ml-1" id="dropdownArrow"></i>
                </button>

                {{-- MENU DROPDOWN --}}
                <div id="exportDropdownMenu"
                    class="hidden absolute right-0 mt-2 w-full sm:w-56 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl shadow-xl dark:shadow-slate-950/40 z-50 py-2 origin-top-right transition-all">

                    <button onclick="triggerExport('kunjungan')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                        <i class="fa-regular fa-file-excel text-emerald-500 text-base w-5"></i> Laporan Kunjungan
                    </button>
                    <button onclick="triggerExport('pengunjung')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                        <i class="fa-solid fa-users text-blue-500 text-base w-5"></i> Laporan Pengunjung
                    </button>
                    <button onclick="triggerExport('kinerja')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                        <i class="fa-solid fa-chart-line text-violet-500 text-base w-5"></i> Laporan Kinerja
                    </button>
                    <button onclick="triggerExport('penolakan')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                        <i class="fa-solid fa-ban text-amber-500 text-base w-5"></i> Laporan Penolakan
                    </button>
                    <button onclick="triggerExport('ulasan')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                        <i class="fa-solid fa-star text-indigo-500 text-base w-5"></i> Laporan Ulasan
                    </button>
                </div>
            </div>

        </div>
    </div>

    {{-- KARTU STATISTIK --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        
        {{-- KARTU 1: TOTAL LAYANAN SELESAI --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between transition-colors duration-300">
            <h3 class="text-slate-400 dark:text-slate-500 text-xs font-bold tracking-wider uppercase mb-3">Total Layanan Selesai</h3>
            <div>
                <div class="text-4xl font-black text-slate-800 dark:text-white tracking-tight mb-1">
                    {{ number_format($totalSelesai, 0, ',', '.') }}
                </div>
                <p class="text-emerald-500 dark:text-emerald-400 text-xs font-bold flex items-start gap-1.5 mt-2 leading-relaxed">
                    <i class="fa-solid fa-circle-check mt-0.5"></i> 
<span>Jumlah antrean yang telah dinyatakan <strong>SELESAI</strong> oleh petugas.</span>
                </p>
            </div>
        </div>

        {{-- KARTU 2: RATA-RATA SLA (DIUBAH MENJADI FORMAT MANUSIAWI) --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between transition-colors duration-300">
            <h3 class="text-slate-400 dark:text-slate-500 text-xs font-bold tracking-wider uppercase mb-3">Rata-Rata Waktu Pelayanan (SLA)</h3>
            <div>
                <div class="text-2xl sm:text-3xl font-black text-slate-800 dark:text-white tracking-tight mb-1">
                    @php
                        // Memastikan angka berupa integer menit kerja riil
                        $totalMenit = (int) $rataRataSla; 
                        $hari = floor($totalMenit / 1440);
                        $sisaMenit = $totalMenit % 1440;
                        $jam = floor($sisaMenit / 60);
                        $menit = $sisaMenit % 60;

                        $hasilSlaReadable = [];
                        if ($hari > 0) $hasilSlaReadable[] = $hari . ' Hari';
                        if ($jam > 0) $hasilSlaReadable[] = $jam . ' Jam';
                        if ($menit > 0 || empty($hasilSlaReadable)) $hasilSlaReadable[] = $menit . ' Menit';
                    @endphp
                    {{ implode(' ', $hasilSlaReadable) }}
                </div>
                <p class="text-blue-500 dark:text-blue-400 text-xs font-bold flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-clock"></i> Kecepatan rata-rata penyelesaian berkas
                </p>
            </div>
        </div>

        {{-- KARTU 3: TINGKAT PENOLAKAN --}}
        <div class="bg-white dark:bg-slate-800 p-6 rounded-2xl border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between transition-colors duration-300">
            <h3 class="text-slate-400 dark:text-slate-500 text-xs font-bold tracking-wider uppercase mb-3">Tingkat Penolakan</h3>
            <div>
                <div class="text-4xl font-black text-slate-800 dark:text-white tracking-tight mb-1">
                    {{ $tingkatPenolakan }}%
                </div>
                <p class="{{ $tingkatPenolakan > 10 ? 'text-rose-500 dark:text-rose-400' : 'text-amber-500 dark:text-amber-400' }} text-xs font-bold flex items-center gap-1 mt-2">
                    <i class="fa-solid fa-circle-xmark"></i> Persentase berkas tidak valid/ditolak
                </p>
            </div>
        </div>
    </div>

    {{-- Bar Chart Bawah --}}
    <div class="bg-white dark:bg-slate-800 p-6 md:p-10 rounded-[2rem] md:rounded-[3rem] border border-gray-50 dark:border-slate-700/50 shadow-sm transition-colors mb-6">
        <h3 class="text-xl font-black text-gray-800 dark:text-white tracking-tight mb-2">Distribusi Kunjungan per Keperluan</h3>
        <div class="h-[340px] md:h-[380px] w-full">
            <canvas id="keperluanChart"></canvas>
        </div>
    </div>

</div>

{{-- MODAL EKSPOR PERIODE (TEMA PREMIUM MATCHING) --}}
<div id="exportModal" class="fixed inset-0 z-[999] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-[1.5rem] md:rounded-[2.5rem] p-6 md:p-10 max-w-md w-full shadow-2xl animate-modal-up relative transition-colors duration-300">

        {{-- HEADER MODAL --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white tracking-tight">Periode Laporan</h3>
                <p class="text-slate-400 dark:text-gray-400 text-xs font-medium mt-1">Tentukan tanggal penarikan data laporan</p>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-300 hover:bg-rose-50 dark:hover:bg-rose-900/50 hover:text-rose-500 dark:hover:text-rose-400 transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        {{-- BANNER INFORMASI / PERHATIAN --}}
        <div class="mb-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
            <p class="text-[10px] uppercase font-black tracking-widest text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-circle-info mr-1"></i> Informasi Ekspor
            </p>
            <p class="text-xs text-indigo-900 dark:text-indigo-300 font-semibold leading-relaxed">
                Data laporan yang diunduh akan disesuaikan otomatis berdasarkan rentang tanggal dan filter aktif pimpinan/prodi Anda.
            </p>
        </div>

        {{-- INPUT TANGGAL PERIODE --}}
        <div class="space-y-5 mb-8">
            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2 tracking-widest">Tanggal Awal</label>
                <div class="relative">
                    <input type="date" id="exportStartDate" required
                        class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
                </div>
            </div>

            <div class="flex flex-col gap-2">
                <label class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase ml-2 tracking-widest">Tanggal Akhir</label>
                <div class="relative">
                    <input type="date" id="exportEndDate" required
                        class="w-full bg-gray-50 dark:bg-gray-700 border-2 border-transparent rounded-2xl p-4 font-bold text-gray-800 dark:text-white focus:bg-white dark:focus:bg-gray-800 focus:border-indigo-500 dark:focus:border-indigo-400 outline-none transition-all">
                </div>
            </div>
        </div>

        {{-- TOMBOL PILIHAN FORMAT DOWNLOAD --}}
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

{{-- MODAL LOADING DOWNLOAD LAPORAN --}}
<div id="loading-modal" class="fixed inset-0 z-[1000] hidden flex items-center justify-center bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-white dark:bg-gray-800 p-6 md:p-8 rounded-[2rem] shadow-2xl border border-slate-100 dark:border-gray-700 flex flex-col items-center max-w-xs w-full text-center animate-modal-up">
        <div class="w-12 h-12 border-4 border-indigo-600 border-t-transparent dark:border-indigo-400 dark:border-t-transparent rounded-full animate-spin mb-4"></div>
        <h3 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-wider mb-1">Menyiapkan Dokumen</h3>
        <p class="text-[11px] text-slate-400 dark:text-gray-400 leading-relaxed">Mohon tunggu, sistem sedang memuat data...</p>
    </div>
</div>

{{-- CARRIER JAVASCRIPT & UTILITIES ENGINE --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
{{-- AMAN: Menambahkan library pendukung datalabels untuk mematangkan chart --}}
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

<script>
let refreshTimer = null; // Tempat menyimpan ID dari setInterval
let isModalOpen = false; // Flag pengunci cadangan

// Fungsi untuk MENYALAKAN auto-refresh (Reset dari nol lagi)
function startAutoRefreshEngine() {
    if (refreshTimer) clearInterval(refreshTimer);

    refreshTimer = setInterval(() => {
        if (!isModalOpen) {
            console.log("[Auto-Refresh] Menjalankan reload halaman...");
            window.location.reload();
        } else {
            console.log("[Auto-Refresh] Tertahan karena modal/loading aktif.");
        }
    }, 180000);
}

// Fungsi untuk MEMATIKAN SEKALIGUS menghancurkan timer refresh
function stopAutoRefreshEngine() {
    if (refreshTimer) {
        clearInterval(refreshTimer);
        refreshTimer = null;
        console.log("[Auto-Refresh] ENGINE MATI TOTAL (Sistem Dihentikan).");
    }
}

// Jalankan pertama kali saat halaman selesai dimuat
document.addEventListener("DOMContentLoaded", function(){
    startAutoRefreshEngine();

    // Event listener klik luar untuk menutup dropdown ekspor secara aman
    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('exportDropdownMenu');
        const trigger = document.getElementById('btnDropdownTrigger');
        if (dropdown && trigger && !trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
            document.getElementById('dropdownArrow').classList.remove('rotate-180');
        }
    });
});

// =======================================================
// LOGIKA JAVASCRIPT DROPDOWN EXPORT
// =======================================================
function toggleExportDropdown() {
    const dropdown = document.getElementById('exportDropdownMenu');
    const arrow = document.getElementById('dropdownArrow');
    if (dropdown && arrow) {
        dropdown.classList.toggle('hidden');
        arrow.classList.toggle('rotate-180');
    }
}

function triggerExport(laporan) {
    stopAutoRefreshEngine(); 
    isModalOpen = true;
    openExportModal(laporan);
    toggleExportDropdown();
}

let exportRoute = '';

function openExportModal(laporan){
    exportRoute = laporan;
    isModalOpen = true;
    stopAutoRefreshEngine(); 

    document.getElementById('exportStartDate').value = '';
    document.getElementById('exportEndDate').value = '';

    const modal = document.getElementById('exportModal');
    modal.remove('hidden');
    modal.classList.add('flex');
}

function closeExportModal(){
    const modal = document.getElementById('exportModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    isModalOpen = false;
    startAutoRefreshEngine(); 
}

document.getElementById('btnExcel').addEventListener('click', function(){
    downloadLaporan('xlsx');
});

document.getElementById('btnPdf').addEventListener('click', function(){
    downloadLaporan('pdf');
});

// =======================================================
// PROSES DOWNLOAD LAPORAN (ANTI BOCOR)
// =======================================================
function downloadLaporan(type){
    const startDate = document.getElementById('exportStartDate').value;
    const endDate = document.getElementById('exportEndDate').value;

    if(!startDate || !endDate){
        alert('Silakan pilih rentang tanggal terlebih dahulu.');
        return;
    }

    stopAutoRefreshEngine();
    isModalOpen = true;

    const loadingModal = document.getElementById('loading-modal');
    if (loadingModal) {
        loadingModal.classList.remove('hidden');
    }

    const modal = document.getElementById('exportModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');

    const prodi = document.querySelector('[name=prodi_id]')?.value ?? '';

    window.location = '/laporan/' + exportRoute +
                      '?type=' + type +
                      '&start_date=' + startDate +
                      '&end_date=' + endDate +
                      '&prodi_id=' + prodi;

    setTimeout(function() {
        if (loadingModal) {
            loadingModal.classList.add('hidden');
        }
        isModalOpen = false;
        startAutoRefreshEngine(); 
    }, 20000);
}

// HANDLER PRODI LOADING (MENGIKUTI LOGIKA CONTOH DENGAN SETTIMEOUT AMAN)
function handleSelectProdiLoading(selectElement) {
    isModalOpen = true;

    const loadingModal = document.getElementById('loading-modal');
    if (loadingModal) {
        loadingModal.classList.remove('hidden');
    }

    selectElement.form.submit();

    setTimeout(function() {
        isModalOpen = false;
    }, 20000);
}
</script>

{{-- SCRIPT RENDERING CHART DENGAN INJEKSI DATA KUNJUNGAN SELALU MUNCUL --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Daftarkan plugin Datalabels ke core Chart.js
        Chart.register(ChartDataLabels);

        // ==========================================
        // CHART DISTRIBUSI KEPERLUAN (BAR)
        // ==========================================
        const ctxKep = document.getElementById('keperluanChart').getContext('2d');
        new Chart(ctxKep, {
            type: 'bar',
            data: {
                labels: {!! json_encode($distribusi_label) !!},
                datasets: [{
                    data: {!! json_encode($distribusi_data) !!},
                    backgroundColor: '#3b82f6',
                    borderRadius: 12,
                    maxBarThickness: 50, // Balok dibuat sedikit tebal agar tegas
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 35 // Ruang kosong di atas balok agar tulisan nilai data tidak terpotong bingkai
                    }
                },
                plugins: { 
                    legend: { display: false },
                    // PENYELARASAN UTAMA: MEMAKSA ANGKA DATASET MUNCUL UTUH TANPA PERLU DI-HOVER
                    datalabels: {
                        anchor: 'end',
                        align: 'top',
                        color: document.documentElement.classList.contains('dark') ? '#f8fafc' : '#0f172a',
                        font: {
                            weight: '900',
                            size: 13 // Ukuran teks angka diperbesar agar mempermudah dosen senior
                        },
                        formatter: function(value) {
                            return value + ' Kunjungan'; // Menambahkan label eksplisit di ujung balok
                        }
                    }
                },
scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: 5, // Memberikan ruang napas di atas grafik jika datanya masih sedikit
                        grid: { 
                            color: document.documentElement.classList.contains('dark') ? '#334155' : '#f1f5f9', 
                            drawBorder: false 
                        },
                        ticks: { 
                            color: document.documentElement.classList.contains('dark') ? '#cbd5e1' : '#475569', 
                            font: { size: 12, weight: '700' },
                            stepSize: 1, // Memaksa kelipatan 1 (1, 2, 3...)
                            precision: 0 // Wajib 0 agar angka desimal (0.2, 0.5) tidak akan pernah muncul
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            color: document.documentElement.classList.contains('dark') ? '#94a3b8' : '#1e293b', 
                            font: { size: 12, weight: '800' }
                        }
                    }
                }
            }
        });
    });
</script>
@endsection