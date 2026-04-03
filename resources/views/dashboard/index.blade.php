@extends('layouts.app')

@section('title', $judul_dashboard)

@section('content')
    {{-- Header Dashboard --}}
    <div class="mb-10">
        <h2 class="text-4xl font-black text-gray-800 tracking-tight leading-none">{{ $judul_dashboard }}</h2>
        <div class="mt-4 flex items-center gap-3">
            <span class="px-4 py-1.5 bg-indigo-100 text-indigo-600 rounded-xl text-[10px] font-black uppercase tracking-widest">
                {{ $user->prodi->nama ?? 'Seluruh Unit Kerja' }}
            </span>
        </div>
    </div>

    {{-- BARIS 1: Statistik Cepat --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="card-stat bg-white p-8 rounded-[32px] shadow-sm border border-gray-50 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Total Antrean</p>
                <h3 class="text-4xl font-black text-gray-800">{{ $data_kunjungan->count() }}</h3>
            </div>
            <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        <div class="card-stat bg-white p-8 rounded-[32px] shadow-sm border border-gray-50 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Sedang Diproses</p>
                <h3 class="text-4xl font-black text-gray-800">{{ $data_kunjungan->where('status_layanan', 'Diproses')->count() }}</h3>
            </div>
            <div class="w-14 h-14 bg-purple-50 text-purple-600 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fa-solid fa-spinner"></i>
            </div>
        </div>

        <div class="card-stat bg-white p-8 rounded-[32px] shadow-sm border border-gray-50 flex items-center justify-between">
            <div>
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-widest mb-1">Selesai</p>
                <h3 class="text-4xl font-black text-gray-800">{{ $data_kunjungan->where('status_layanan', 'Selesai')->count() }}</h3>
            </div>
            <div class="w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center text-2xl">
                <i class="fa-solid fa-check-double"></i>
            </div>
        </div>
    </div>

    {{-- BARIS 2: Analytics KPI (Hanya muncul jika diakses melalui role non-admin atau data dikirim) --}}
    @if(isset($skor_kepuasan))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        {{-- Grafik Tren SLA --}}
        <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-gray-50 shadow-sm">
            <h3 class="text-xl font-black text-gray-800 mb-6">Tren Waktu Layanan (SLA)</h3>
            <div class="h-[300px]">
                <canvas id="slaChart"></canvas>
            </div>
        </div>

        {{-- Grafik Skor Kepuasan --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-50 shadow-sm flex flex-col items-center justify-center">
            <h3 class="text-xl font-black text-gray-800 mb-4 self-start">Kepuasan</h3>
            <div class="relative w-full max-w-[220px]">
                <canvas id="satisfactionChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-4xl font-black text-gray-800">{{ $skor_kepuasan['persen'] }}%</span>
                    <span class="text-[10px] font-bold text-emerald-500 uppercase">Puas</span>
                </div>
            </div>
            <div class="flex justify-between w-full mt-6 text-center">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Puas</p>
                    <p class="font-black text-emerald-500">{{ $skor_kepuasan['puas'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Cukup</p>
                    <p class="font-black text-amber-500">{{ $skor_kepuasan['cukup'] }}</p>
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase">Kurang</p>
                    <p class="font-black text-rose-500">{{ $skor_kepuasan['kurang'] }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- BARIS 3: Daftar Kartu Antrean --}}
    <div class="flex items-center gap-3 mb-8">
        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Antrean Aktif</h3>
        <div class="h-[2px] flex-1 bg-gray-100 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($data_kunjungan as $k)
            <div class="bg-white border {{ $k->status_layanan == 'Diproses' ? 'border-indigo-500 ring-4 ring-indigo-50' : 'border-gray-100' }} rounded-[32px] p-8 transition-all hover:shadow-xl">
                <div class="flex justify-between items-start mb-6">
                    <span class="px-4 py-1 {{ $k->status_layanan == 'Diproses' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-500' }} rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $k->status_layanan }}
                    </span>
                    <h4 class="text-3xl font-black text-gray-800">{{ $k->nomor_kunjungan }}</h4>
                </div>

                <div class="mb-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pengunjung</p>
                    <h5 class="text-xl font-black text-gray-800 leading-tight">{{ $k->pengunjung->nama_lengkap }}</h5>
                    <p class="text-xs font-bold text-indigo-500">{{ $k->pengunjung->asal_instansi }}</p>
                </div>

                <div class="p-4 bg-gray-50 rounded-2xl mb-8">
                    <p class="text-[9px] font-bold text-gray-400 uppercase mb-1">Keperluan</p>
                    <p class="text-sm font-bold text-gray-700">{{ $k->keperluan_master->keterangan ?? $k->keperluan }}</p>
                </div>

                <div class="flex gap-3">
                    @if($k->status_layanan == 'Antre')
                        <button onclick="bukaModalProses('{{ $k->nomor_kunjungan }}')" class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-colors">
                            Mulai Proses
                        </button>
                    @elseif($k->status_layanan == 'Diproses')
                        <form action="{{ route('kunjungan.selesai', $k->nomor_kunjungan) }}" method="POST" class="w-full">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-colors">
                                Selesaikan
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <p class="text-gray-400 font-bold italic">Tidak ada data antrean untuk ditampilkan.</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL PROSES SLA --}}
    <div id="modalProsesSLA" class="fixed inset-0 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="tutupModal()"></div>
            <div class="relative bg-white rounded-[2.5rem] p-10 max-w-md w-full shadow-2xl">
                <h3 class="text-2xl font-black mb-6">Tentukan Estimasi Waktu</h3>
                <form id="formSLA" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <input type="number" name="estimasi_sla" placeholder="Contoh: 15" class="bg-gray-50 border-none rounded-2xl p-4 font-bold" required>
                        <select name="satuan_sla" class="bg-gray-50 border-none rounded-2xl p-4 font-bold">
                            <option value="Menit">Menit</option>
                            <option value="Hari">Hari</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase">Konfirmasi & Mulai</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function bukaModalProses(noAntrean) {
        const form = document.getElementById('formSLA');
        form.action = `/dashboard/mulai-proses/${noAntrean}`;
        document.getElementById('modalProsesSLA').classList.remove('hidden');
    }

    function tutupModal() {
        document.getElementById('modalProsesSLA').classList.add('hidden');
    }

    @if(isset($skor_kepuasan))
    // Chart Kepuasan
    new Chart(document.getElementById('satisfactionChart'), {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $skor_kepuasan['puas'] }}, {{ $skor_kepuasan['cukup'] }}, {{ $skor_kepuasan['kurang'] }}],
                backgroundColor: ['#10b981', '#f59e0b', '#f43f5e'],
                borderWidth: 0, cutout: '85%', borderRadius: 20
            }]
        },
        options: { plugins: { legend: { display: false } } }
    });

    // Chart SLA
    new Chart(document.getElementById('slaChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode($label_sla) !!},
            datasets: [{
                label: 'Tepat Waktu',
                data: {!! json_encode($data_tepat_waktu) !!},
                borderColor: '#6366f1',
                backgroundColor: 'rgba(99, 102, 241, 0.1)',
                fill: true, tension: 0.4
            }, {
                label: 'Terlambat',
                data: {!! json_encode($data_terlambat) !!},
                borderColor: '#f43f5e',
                fill: false, tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
    @endif
</script>
@endpush
