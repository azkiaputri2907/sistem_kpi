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

    {{-- BARIS 2: Analytics KPI --}}
    @if(isset($skor_kepuasan))
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
        <div class="lg:col-span-2 bg-white p-8 rounded-[2.5rem] border border-gray-50 shadow-sm">
            <h3 class="text-xl font-black text-gray-800 mb-6">Tren Waktu Layanan (SLA)</h3>
            <div class="h-[300px]">
                <canvas id="slaChart"></canvas>
            </div>
        </div>

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
                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Puas</p><p class="font-black text-emerald-500">{{ $skor_kepuasan['puas'] }}</p></div>
                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Cukup</p><p class="font-black text-amber-500">{{ $skor_kepuasan['cukup'] }}</p></div>
                <div><p class="text-[10px] font-bold text-gray-400 uppercase">Kurang</p><p class="font-black text-rose-500">{{ $skor_kepuasan['kurang'] }}</p></div>
            </div>
        </div>
    </div>
    @endif

    {{-- BARIS 3: Daftar Kartu Antrean --}}
    <div class="flex items-center gap-3 mb-8">
        <h3 class="text-xl font-black text-gray-800 uppercase tracking-tighter">Daftar Antrean</h3>
        <div class="h-[2px] flex-1 bg-gray-100 rounded-full"></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($data_kunjungan as $k)
            @php
                $isSelesai = $k->status_layanan == 'Selesai';
                $isDiproses = $k->status_layanan == 'Diproses';
            @endphp

            <div class="bg-white border {{ $isDiproses ? 'border-indigo-500 ring-4 ring-indigo-50' : ($isSelesai ? 'border-emerald-100 bg-gray-50/30' : 'border-gray-100') }} rounded-[32px] p-8 transition-all hover:shadow-xl relative overflow-hidden">

                {{-- Indikator Centang jika Selesai --}}
                @if($isSelesai)
                    <div class="absolute top-0 right-0 p-6">
                        <i class="fa-solid fa-circle-check text-emerald-400 text-2xl opacity-50"></i>
                    </div>
                @endif

                <div class="flex justify-between items-start mb-6">
                    <span class="px-4 py-1 {{ $isDiproses ? 'bg-indigo-600 text-white' : ($isSelesai ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500') }} rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $k->status_layanan }}
                    </span>
                    <h4 class="text-3xl font-black {{ $isSelesai ? 'text-gray-300' : 'text-gray-800' }}">{{ $k->nomor_kunjungan }}</h4>
                </div>

                <div class="mb-6">
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mb-1">Pengunjung</p>
                    <h5 class="text-xl font-black {{ $isSelesai ? 'text-gray-400' : 'text-gray-800' }} leading-tight">{{ $k->pengunjung->nama_lengkap }}</h5>
                    <p class="text-xs font-bold {{ $isSelesai ? 'text-gray-400' : 'text-indigo-500' }}">{{ $k->pengunjung->asal_instansi }}</p>
                </div>

                <div class="p-4 {{ $isSelesai ? 'bg-white' : 'bg-gray-50' }} rounded-2xl mb-8">
                    <p class="text-[9px] font-bold text-gray-400 uppercase mb-1">Keperluan</p>
                    <p class="text-sm font-bold {{ $isSelesai ? 'text-gray-400' : 'text-gray-700' }}">{{ $k->keperluan_master->keterangan ?? 'Lainnya' }}</p>

                    @if($isSelesai && $k->waktu_selesai_layanan)
                        <div class="mt-2 pt-2 border-t border-emerald-50">
                            <p class="text-[9px] font-bold text-emerald-500 uppercase mb-1">Waktu Selesai</p>
                            <p class="text-[11px] font-black text-emerald-600">{{ \Carbon\Carbon::parse($k->waktu_selesai_layanan)->format('H:i') }} WIB</p>
                        </div>
                    @elseif($k->keperluan)
                        <div class="mt-2 pt-2 border-t border-gray-200/50">
                            <p class="text-[11px] font-medium text-gray-600 italic">"{{ $k->keperluan }}"</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    @if($k->status_layanan == 'Antre')
                        <button onclick="bukaModalProses('{{ $k->nomor_kunjungan }}')"
                                class="flex-1 bg-indigo-600 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-colors shadow-lg shadow-indigo-100">
                            Mulai Proses
                        </button>
                    @elseif($isDiproses)
                        <form id="form-selesai-{{ $k->id }}" action="{{ route('kunjungan.selesai', $k->id) }}" method="POST" class="w-full">
                            @csrf
                            <button type="button" onclick="konfirmasiSelesai('{{ $k->id }}', '{{ $k->nomor_kunjungan }}')"
                                    class="w-full bg-emerald-500 text-white py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100 flex items-center justify-center gap-2">
                                <i class="fa-solid fa-check-circle"></i> Selesaikan
                            </button>
                        </form>
                    @else
                        <div class="w-full bg-gray-100 text-gray-400 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest text-center">
                            <i class="fa-solid fa-lock mr-1"></i> Layanan Selesai
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <i class="fa-solid fa-folder-open text-4xl text-gray-200 mb-4"></i>
                <p class="text-gray-400 font-bold italic">Tidak ada data antrean aktif.</p>
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
                    <button type="submit" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black uppercase shadow-lg shadow-indigo-100">Konfirmasi & Mulai</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    // 1. Konfirmasi Selesai dengan SweetAlert2
    function konfirmasiSelesai(id, nomor) {
        Swal.fire({
            title: 'Selesaikan Antrean?',
            text: "Antrean " + nomor + " akan ditandai sebagai selesai.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#f43f5e',
            confirmButtonText: 'Ya, Selesai!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            borderRadius: '2rem',
            customClass: {
                popup: 'rounded-[2.5rem]',
                confirmButton: 'rounded-xl px-5 py-3 font-bold uppercase text-[10px] tracking-widest',
                cancelButton: 'rounded-xl px-5 py-3 font-bold uppercase text-[10px] tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading() }
                });
                document.getElementById('form-selesai-' + id).submit();
            }
        })
    }

    // 2. Alert Sukses dari Session
    @if(session('success'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: "{{ session('success') }}",
            showConfirmButton: false,
            timer: 2500,
            borderRadius: '2rem',
            customClass: { popup: 'rounded-[2.5rem]' }
        });
    @endif

    // 3. Fungsi Modal SLA
    function bukaModalProses(noAntrean) {
        const form = document.getElementById('formSLA');
        form.action = `/dashboard/mulai-proses/${noAntrean}`;
        document.getElementById('modalProsesSLA').classList.remove('hidden');
    }

    function tutupModal() {
        document.getElementById('modalProsesSLA').classList.add('hidden');
    }

    // 4. Inisialisasi Grafik (Jika Ada)
    @if(isset($skor_kepuasan))
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
