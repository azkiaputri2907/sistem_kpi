@extends('layouts.app')
<style>
    .swal2-backdrop-show {
        backdrop-filter: blur(8px) !important;
        -webkit-backdrop-filter: blur(8px) !important;
        background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
    }
</style>

@section('content')
{{-- Header Dashboard --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 md:mb-10">
    <div>
<h2 class="text-3xl md:text-4xl font-black text-gray-800 dark:text-white tracking-tight">Pantauan Kinerja</h2>
        <p class="text-slate-400 dark:text-slate-400 text-xs md:text-sm font-medium mt-1 md:mt-2">Ringkasan evaluasi dan performa layanan.</p>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
        @if($user->role_id == 1 || $user->role_id == 3)
            <form action="{{ route('dashboard.analytics') }}" method="GET" class="w-full sm:w-auto">
                <div class="relative w-full">
                    <select name="prodi_id"
                        onchange="this.form.submit()"
                        class="w-full sm:w-auto bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 pr-10 text-xs font-black text-slate-700 dark:text-slate-200 shadow-sm outline-none appearance-none transition-colors">
                        <option value="" class="dark:bg-slate-800">Seluruh Program Studi</option>
                        @foreach($daftar_prodi as $p)
                            <option value="{{ $p->id }}" {{ request('prodi_id') == $p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                                {{ $p->nama }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 pointer-events-none text-[10px]">
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </form>
        @else
            <div class="bg-white dark:bg-slate-800 px-5 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-xs font-black text-slate-700 dark:text-slate-200 shadow-sm flex items-center justify-center gap-2 transition-colors">
                {{ $user->prodi->nama ?? 'Program Studi' }}
            </div>
        @endif

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
                class="hidden absolute right-0 mt-2 w-full sm:w-56 bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700 rounded-2xl shadow-xl z-50 py-2 origin-top-right transition-all">
                <button onclick="triggerExport('kunjungan')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                    <i class="fa-regular fa-file-excel text-emerald-500 text-base w-5"></i> Laporan Kunjungan
                </button>
                <button onclick="triggerExport('pengunjung')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                    <i class="fa-solid fa-users text-blue-500 text-base w-5"></i> Laporan Pengunjung
                </button>
                <button onclick="triggerExport('kinerja')" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors text-left">
                    <i class="fa-solid fa-chart-line text-violet-500 text-base w-5"></i> Laporan Kinerja
                </button>
            </div>
        </div>
    </div>
</div>

{{-- CARD STATISTIK --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">

    {{-- TOTAL KUNJUNGAN + TARGET TRACKER --}}
    <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Kuantitas Layanan
                </p>
                <div class="flex items-baseline gap-2">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                        {{ $total_dilayani ?? $total_kunjungan }}
                    </h2>
                    <span class="text-xs font-bold text-slate-400 dark:text-slate-500">
                        / {{ $target_tamu ?? 10 }} Selesai
                    </span>
                </div>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        {{-- Progress Bar Target --}}
        @php
            $realisasi = $total_dilayani ?? $total_kunjungan;
            $target = $target_tamu ?? 10;
            $persenKuantitas = $target > 0 ? min(100, round(($realisasi / $target) * 100)) : 0;
        @endphp
        <div class="mt-2">
            <div class="flex justify-between items-center text-[11px] font-bold mb-1.5">
                <span class="text-slate-400 dark:text-slate-500">Progres Target Harian</span>
                <span class="text-blue-600 dark:text-blue-400">{{ $persenKuantitas }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-100 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-blue-500 dark:bg-blue-600 rounded-full transition-all duration-500" style="width: {{ $persenKuantitas }}%"></div>
            </div>
        </div>
    </div>

    {{-- SLA (EFEKTIVITAS) --}}
    <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Efektivitas (SLA)
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                    {{ $efektivitas_persen ?? $efektivitas }}%
                </h2>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-purple-100 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>
        <div class="mt-auto pt-2 border-t border-slate-50 dark:border-slate-700/30 flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
            <i class="fa-solid fa-circle-check text-emerald-500 text-[10px]"></i>
            <span>Mengukur ketepatan waktu durasi pelayanan</span>
        </div>
    </div>

    {{-- SURVEI (KUALITAS) --}}
    <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex flex-col justify-between hover:shadow-md transition-all duration-300 group sm:col-span-2 lg:col-span-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Kualitas (Survei)
                </p>
                <div class="flex items-center gap-2">
                    <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                        {{ $kualitas_rating }}
                    </h2>
                    <div class="flex text-amber-400 text-xs gap-0.5 mb-1">
                        @if(is_numeric($kualitas_rating) && $kualitas_rating > 0)
                            @for($i = 1; $i <= 5; $i++)
                                <i class="{{ $i <= round($kualitas_rating) ? 'fa-solid' : 'fa-regular' }} fa-star"></i>
                            @endfor
                        @else
                            <span class="text-slate-400 dark:text-slate-600 font-bold text-xs">Belum ada ulasan</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-100 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 flex items-center justify-center text-xl sm:text-2xl shrink-0 group-hover:scale-105 transition-transform">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>
        <div class="mt-auto pt-2 border-t border-slate-50 dark:border-slate-700/30 flex items-center gap-1.5 text-[11px] font-bold text-slate-400 dark:text-slate-500">
            <i class="fa-solid fa-heart text-rose-500 text-[10px]"></i>
            <span>Berdasarkan akumulasi indeks kepuasan tamu</span>
        </div>
    </div>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8 mb-6 md:mb-8">
    {{-- Grafik Tren Waktu Layanan (SLA) --}}
    <div class="lg:col-span-2 bg-white dark:bg-slate-800 p-5 md:p-8 rounded-[2rem] md:rounded-[3rem] border border-gray-50 dark:border-slate-700/50 shadow-sm relative transition-colors">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-white tracking-tight">Tren Waktu Layanan (SLA)</h3>
                <p class="text-[10px] font-black text-slate-300 dark:text-slate-500 uppercase tracking-widest mt-1">Rata-rata menit per hari</p>
            </div>
            
        </div>
        <div class="h-[280px] md:h-[320px]">
            <canvas id="slaChart"></canvas>
        </div>
    </div>

    {{-- Grafik Skor Kepuasan --}}
    <div class="bg-white dark:bg-slate-800 p-5 md:p-8 rounded-[2rem] md:rounded-[3rem] border border-gray-50 dark:border-slate-700/50 shadow-sm flex flex-col items-center relative transition-colors">
        <h3 class="text-lg md:text-xl font-black text-gray-800 dark:text-white mb-6 md:mb-10 self-start tracking-tight">Skor Kepuasan</h3>

        <div class="relative w-full aspect-square max-w-[200px] md:max-w-[240px]">
            <canvas id="satisfactionChart"></canvas>
            <div class="absolute inset-0 flex flex-col items-center justify-center">
                <span class="text-4xl md:text-5xl font-black text-gray-800 dark:text-white tracking-tighter">{{ $skor_kepuasan['persen'] }}%</span>
                <span class="text-[10px] font-black text-indigo-500 dark:text-indigo-400 uppercase mt-1 tracking-widest">Puas</span>
            </div>
        </div>

        {{-- Legenda Skor Kepuasan --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 md:gap-4 mt-6 md:mt-8 w-full">
            <div class="flex flex-col items-center p-2 rounded-xl bg-slate-50/50 dark:bg-slate-700/30 sm:bg-transparent sm:dark:bg-transparent">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-indigo-600"></div>
                    <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase text-center">Sangat Puas</span>
                </div>
                <div class="font-black text-gray-700 dark:text-gray-200">{{ $skor_kepuasan['sangat_puas'] }}</div>
            </div>

            <div class="flex flex-col items-center p-2 rounded-xl bg-slate-50/50 dark:bg-slate-700/30 sm:bg-transparent sm:dark:bg-transparent sm:border-l sm:border-gray-100 sm:dark:border-slate-700">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                    <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase text-center">Puas</span>
                </div>
                <div class="font-black text-gray-700 dark:text-gray-200">{{ $skor_kepuasan['puas'] }}</div>
            </div>

            <div class="flex flex-col items-center p-2 rounded-xl bg-slate-50/50 dark:bg-slate-700/30 sm:bg-transparent sm:dark:bg-transparent sm:border-l sm:border-gray-100 sm:dark:border-slate-700">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-amber-400"></div>
                    <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase text-center">Kurang Puas</span>
                </div>
                <div class="font-black text-gray-700 dark:text-gray-200">{{ $skor_kepuasan['kurang_puas'] }}</div>
            </div>

            <div class="flex flex-col items-center p-2 rounded-xl bg-slate-50/50 dark:bg-slate-700/30 sm:bg-transparent sm:dark:bg-transparent sm:border-l sm:border-gray-100 sm:dark:border-slate-700">
                <div class="flex items-center gap-1.5 mb-1">
                    <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                    <span class="text-[9px] font-bold text-gray-400 dark:text-slate-500 uppercase text-center">Tidak Puas</span>
                </div>
                <div class="font-black text-gray-700 dark:text-gray-200">{{ $skor_kepuasan['tidak_puas'] }}</div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL EKSPOR PERIODE --}}
<div id="exportModal" class="fixed inset-0 z-[999] hidden bg-gray-900/60 dark:bg-black/70 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-[1.5rem] md:rounded-[2.5rem] p-6 md:p-10 max-w-md w-full shadow-2xl animate-modal-up relative transition-colors duration-300">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white tracking-tight">Periode Laporan</h3>
                <p class="text-slate-400 dark:text-gray-400 text-xs font-medium mt-1">Tentukan tanggal penarikan data laporan</p>
            </div>
            <button type="button" onclick="closeExportModal()" class="w-8 h-8 md:w-10 md:h-10 flex items-center justify-center rounded-full bg-gray-50 dark:bg-gray-700 text-gray-400 dark:text-gray-300 hover:bg-rose-50 dark:hover:bg-rose-900/50 hover:text-rose-500 dark:hover:text-rose-400 transition-all">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="mb-6 p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50">
            <p class="text-[10px] uppercase font-black tracking-widest text-indigo-600 dark:text-indigo-400 mb-1">
                <i class="fa-solid fa-circle-info mr-1"></i> Informasi Ekspor
            </p>
            <p class="text-xs text-indigo-900 dark:text-indigo-300 font-semibold leading-relaxed">
                Data laporan yang diunduh akan disesuaikan otomatis berdasarkan rentang tanggal dan filter aktif pimpinan/prodi Anda.
            </p>
        </div>

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
        <p class="text-[11px] text-slate-400 dark:text-gray-400 leading-relaxed">Mohon tunggu, sistem sedang merangkum data laporan...</p>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? '#334155' : '#f1f5f9';
    const textColorPrimary = isDark ? '#cbd5e1' : '#64748b';

    // ==========================================
    // 1. CHART KEPUASAN (DOUGHNUT)
    // ==========================================
    const ctxSat = document.getElementById('satisfactionChart').getContext('2d');
    new Chart(ctxSat, {
        type: 'doughnut',
        data: {
            labels: ['Sangat Puas', 'Puas', 'Kurang Puas', 'Tidak Puas'],
            datasets: [{
                data: [
                    {{ $skor_kepuasan['sangat_puas'] }},
                    {{ $skor_kepuasan['puas'] }},
                    {{ $skor_kepuasan['kurang_puas'] }},
                    {{ $skor_kepuasan['tidak_puas'] }}
                ],
                backgroundColor: ['#4f46e5', '#10b981', '#fbbf24', '#f43f5e'],
                borderWidth: 0,
                cutout: '82%',
                borderRadius: 15,
                spacing: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // ==========================================
    // 3. CHART TREN SLA (LINE)
    // ==========================================
    const ctxSla = document.getElementById('slaChart').getContext('2d');
    const gradientIndigo = ctxSla.createLinearGradient(0, 0, 0, 400);
    gradientIndigo.addColorStop(0, isDark ? 'rgba(99, 102, 241, 0.15)' : 'rgba(99, 102, 241, 0.3)');
    gradientIndigo.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctxSla, {
        type: 'line',
        data: {
            labels: {!! json_encode($label_sla) !!},
            datasets: [
                {
                    label: 'Tepat Waktu',
                    data: {!! json_encode($data_tepat_waktu) !!},
                    borderColor: '#6366f1',
                    backgroundColor: gradientIndigo,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 4,
                    pointRadius: 4,
                    pointBackgroundColor: '#6366f1'
                },
                {
                    label: 'Terlambat',
                    data: {!! json_encode($data_terlambat) !!},
                    borderColor: '#f43f5e',
                    fill: false,
                    tension: 0.4,
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 4,
                    pointBackgroundColor: '#f43f5e'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    suggestedMax: 5,
                    ticks: { stepSize: 1, color: textColorPrimary },
                    grid: { color: gridColor }
                },
                x: {
                    ticks: { color: textColorPrimary },
                    grid: { display: false }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    align: 'end',
                    labels: { color: textColorPrimary, font: { weight: '600' } }
                }
            }
        }
    });

    window.addEventListener('click', function(e) {
        const dropdown = document.getElementById('exportDropdownMenu');
        const trigger = document.getElementById('btnDropdownTrigger');
        if (dropdown && trigger && !trigger.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
            document.getElementById('dropdownArrow').classList.remove('rotate-180');
        }
    });
});

let exportRoute = '';
let isModalOpen = false;

function toggleExportDropdown() {
    const dropdown = document.getElementById('exportDropdownMenu');
    const arrow = document.getElementById('dropdownArrow');
    if (dropdown) dropdown.classList.toggle('hidden');
    if (arrow) arrow.classList.toggle('rotate-180');
}

function triggerExport(laporan) {
    toggleExportDropdown();
    openExportModal(laporan);
}

function openExportModal(laporan) {
    exportRoute = laporan;
    document.getElementById('exportStartDate').value = '';
    document.getElementById('exportEndDate').value = '';
    isModalOpen = true;
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

function closeExportModal() {
    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    isModalOpen = false;
}

document.addEventListener('DOMContentLoaded', function() {
    const btnExcel = document.getElementById('btnExcel');
    const btnPdf = document.getElementById('btnPdf');
    if (btnExcel) btnExcel.addEventListener('click', function() { downloadLaporan('xlsx'); });
    if (btnPdf) btnPdf.addEventListener('click', function() { downloadLaporan('pdf'); });
});

function downloadLaporan(type) {
    const startDate = document.getElementById('exportStartDate').value;
    const endDate = document.getElementById('exportEndDate').value;

    if (!startDate || !endDate) {
        alert('Silakan pilih rentang tanggal terlebih dahulu.');
        return;
    }

    const prodi = document.querySelector('[name=prodi_id]')?.value ?? '';
    const loadingModal = document.getElementById('loading-modal');
    if (loadingModal) loadingModal.classList.remove('hidden');

    const modal = document.getElementById('exportModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    isModalOpen = true;

    window.location = '/laporan/' + exportRoute +
                      '?type=' + type +
                      '&start_date=' + startDate +
                      '&end_date=' + endDate +
                      '&prodi_id=' + prodi;

    setTimeout(function() {
        if (loadingModal) loadingModal.classList.add('hidden');
        isModalOpen = false;
    }, 15000);
}
</script>

{{-- SCRIPT RENDERING CHART --}}
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('kinerjaChart').getContext('2d');

        const chartLabels = {!! json_encode($labels) !!};
        let chartDatasets = {!! json_encode($chartDatasets) !!};

        const isDarkMode = document.documentElement.classList.contains('dark');
        const textColor = isDarkMode ? '#94a3b8' : '#64748b';
        const gridColor = isDarkMode ? 'rgba(148, 163, 184, 0.1)' : 'rgba(100, 116, 139, 0.05)';

        chartDatasets = chartDatasets.map(function(dataset) {
            const dynamicColors = dataset.data.map(function(nilai) {
                if (nilai >= 90) return '#3b82f6';
                else if (nilai >= 76) return '#10b981';
                else if (nilai >= 60) return '#f59e0b';
                else if (nilai > 0) return '#ef4444';
                else return '#6b7280'; // Abu-abu default
            });

            const dynamicBorders = dataset.data.map(function(nilai) {
                if (nilai >= 90) return '#2563eb';
                else if (nilai >= 76) return '#059669';
                else if (nilai >= 60) return '#d97706';
                else if (nilai > 0) return '#dc2626';
                else return '#4b5563';
            });

            dataset.backgroundColor = dynamicColors;
            dataset.borderColor = dynamicBorders;
            dataset.borderWidth = 1;
            dataset.borderRadius = 6;

            return dataset;
        });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: chartDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor,
                            font: { family: 'Plus Jakarta Sans, sans-serif', weight: '600', size: 11 },
                            boxWidth: 10,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 20,

                            // =============================================================
                            // TAMBAHAN SOLUSI: Menyelaraskan Warna Bulatan Legenda Aktif
                            // =============================================================
                            generateLabels: function(chart) {
                                const defaults = Chart.defaults.plugins.legend.labels.generateLabels(chart);
                                defaults.forEach(function(label, index) {
                                    const dataset = chart.data.datasets[index];
                                    if (Array.isArray(dataset.backgroundColor)) {
                                        // Cari warna di dalam array yang BUKAN abu-abu default (#6b7280)
                                        const warnaAktif = dataset.backgroundColor.find(function(color) {
                                            return color !== '#6b7280';
                                        });
                                        // Jika ada hari yang aktif datanya (seperti hari Rabu), gunakan warna itu.
                                        // Jika kosong semua, biarkan default abu-abu.
                                        label.fillStyle = warnaAktif ? warnaAktif : '#6b7280';
                                        label.strokeStyle = warnaAktif ? warnaAktif : '#4b5563';
                                    }
                                });
                                return defaults;
                            }
                        }
                    },
                    tooltip: {
                        padding: 12,
                        backgroundColor: isDarkMode ? '#1e293b' : '#0f172a',
                        titleFont: { size: 13, weight: 'bold' },
                        bodyFont: { size: 13 },
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.raw !== null) label += context.raw;
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: false,
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 11 } }
                    },
                    y: {
                        stacked: false,
                        grid: { color: gridColor },
                        min: 0,
                        max: 100,
                        ticks: {
                            color: textColor,
                            font: { size: 11 },
                            callback: function(value) { return value; }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
