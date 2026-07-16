@extends('layouts.app')

<style>
    .swal2-backdrop-show {
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    background-color: rgba(15, 23, 42, 0.4) !important; /* Warna gelap transparan tipis */
}
</style>

@section('title', $judul_dashboard)

@section('content')
<div class="min-h-screen bg-[#f6f7fb] dark:bg-slate-900 px-4 sm:px-6 lg:px-8 py-6 transition-colors duration-300">

    {{-- HEADER --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-8">
        <div class="space-y-2">
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-slate-900 dark:text-white leading-tight">
                Dashboard Admin
            </h1>

            <div class="flex flex-wrap items-center gap-2">
                <span class="px-4 py-1.5 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] sm:text-[11px] font-black flex items-center gap-2 shadow-sm">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    @if($user->role_id==1 || $user->role_id==3)
                        Monitoring Active
                    @else
                        Petugas Aktif
                    @endif
                </span>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto">

            @php
                $isSuper=$user->role_id==1 || $user->role_id==3;
            @endphp

            <div class="relative w-full sm:w-auto">
                <select onchange="filterProdi(this.value)"
                    {{ !$isSuper ? 'disabled' : '' }}
                    class="w-full sm:w-[250px] md:w-[280px] bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3.5 text-sm font-bold text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none appearance-none transition-all shadow-sm {{ !$isSuper ? 'bg-slate-100 dark:bg-slate-800/50 cursor-not-allowed text-slate-500 dark:text-slate-500' : '' }}">

                    @if($isSuper)
                        <option value="" class="dark:bg-slate-800">🌍 Seluruh Program Studi</option>
                        @foreach($daftar_prodi as $p)
                            <option value="{{ $p->id }}" {{ request('prodi_id')==$p->id ? 'selected' : '' }} class="dark:bg-slate-800">
                                🎓 {{ $p->nama }}
                            </option>
                        @endforeach
                    @else
                        <option selected class="dark:bg-slate-800">
                            🎓 {{ $user->prodi->nama ?? 'Prodi Tidak Ditemukan' }}
                        </option>
                    @endif

                </select>

                <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400 text-xs">
                    <i class="fa-solid fa-chevron-down"></i>
                </div>
            </div>

            {{-- TOMBOL EKSPOR --}}
          <button type="button" onclick="openExportModal()"
    class="bg-gradient-to-r from-[#0b3a82] via-[#1e293b] to-red-600 text-white px-6 py-3.5 sm:py-3 rounded-2xl font-black text-sm shadow-lg hover:scale-[1.02] transition-all text-center">
    <i class="fa-solid fa-file-export mr-2"></i>
    Laporan Pengunjung
</button>

        </div>
    </div>

    {{-- CARD STATISTIK --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-10">

        {{-- TOTAL --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Total Kunjungan
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                    {{ $total_kunjungan }}
                </h2>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                <i class="fa-solid fa-users"></i>
            </div>
        </div>

        {{-- SLA --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Efektivitas (SLA)
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                    {{ $efektivitas_persen }}%
                </h2>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-purple-100 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                <i class="fa-solid fa-clock"></i>
            </div>
        </div>

        {{-- SURVEI --}}
        <div class="bg-white dark:bg-slate-800 rounded-[2rem] p-6 border border-slate-100 dark:border-slate-700/60 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-[10px] sm:text-[11px] uppercase font-black tracking-widest text-slate-400 dark:text-slate-500 mb-1">
                    Kualitas (Survei)
                </p>
                <h2 class="text-3xl sm:text-4xl font-black text-slate-900 dark:text-white">
                    {{ $kualitas_rating }}
                </h2>
            </div>
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl sm:rounded-3xl bg-amber-100 dark:bg-amber-950/40 text-amber-500 dark:text-amber-400 flex items-center justify-center text-xl sm:text-2xl shrink-0">
                <i class="fa-solid fa-star"></i>
            </div>
        </div>

    </div>

    {{-- TITLE SECTION ANTREAN --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 w-full">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-8 bg-indigo-600 rounded-full"></div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Antrean Layanan
            </h3>
        </div>

        @if($data_kunjungan->count() > 6)
            <a href="{{ route('dashboard.antrean') }}" class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-black text-xs uppercase tracking-widest rounded-2xl shadow-sm transition-all flex items-center justify-center gap-2 shrink-0">
                <span>Lihat semua antrean</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        @endif
    </div>

    {{-- LIST ANTREAN --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        @forelse($data_kunjungan->take(6) as $k)

            @php
                $isAntre = $k->status_layanan == 'Antre';
                $isDiproses = $k->status_layanan == 'Diproses';
                $isSelesai = $k->status_layanan == 'Selesai';
                $isDitolak = $k->status_layanan == 'Ditolak';

                $borderClass = $isDitolak
                    ? 'border-rose-500 bg-rose-50/20 dark:bg-rose-950/10'
                    : ($isAntre
                        ? 'border-amber-300 dark:border-amber-500/50'
                        : ($isDiproses
                            ? 'border-blue-300 dark:border-blue-500/50'
                            : 'border-emerald-300 dark:border-emerald-500/50'
                        )
                    );
                $badgeClass = $isAntre ? 'bg-amber-100 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400' : ($isDiproses ? 'bg-blue-100 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400' : 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400');
            @endphp

            <div class="bg-white dark:bg-slate-800 rounded-[2rem] border-2 {{ $borderClass }} p-4 sm:p-5 shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col group relative overflow-hidden">

                <div class="absolute -right-4 -top-4 w-20 h-20 bg-slate-50 dark:bg-slate-700/30 rounded-full -z-0 group-hover:scale-150 transition-transform duration-500"></div>

                <div class="relative z-10 flex flex-col h-full">
                    {{-- HEADER CARD --}}
                    <div class="flex items-start justify-between gap-3 mb-4">
                        <div class="flex-1">
                            <span class="px-2.5 py-1 rounded-full text-[9px] font-black uppercase tracking-widest
                                {{ $k->status_layanan == 'Ditolak'
                                    ? 'bg-rose-100 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 ring-1 ring-rose-200 dark:ring-rose-800'
                                    : $badgeClass }}">
                                {{ $k->status_layanan }}
                            </span>
                           <h4 class="mt-3 text-lg font-black text-slate-900 dark:text-white leading-tight">
                                {{ $k->nama_lengkap ?? $k->pengunjung?->nama_lengkap ?? 'Pengunjung' }}
                            </h4>
                                 <p class="text-xs font-bold text-slate-400 dark:text-slate-500 mt-0.5">
                                <i class="fa-solid fa-building text-[10px] mr-1"></i> {{ $k->pengunjung->asal_instansi ?? '-' }}
                            </p>
                        </div>
                        <div class="text-right shrink-0">
                            <h2 class="text-xs sm:text-sm font-black text-slate-880 dark:text-slate-200 tracking-tight bg-slate-100 dark:bg-slate-700 px-2.5 py-1 rounded-xl inline-block">
                                {{ $k->nomor_kunjungan }}
                            </h2>
                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-black mt-1 pr-1">
                                <i class="fa-regular fa-clock mr-1"></i>{{ $k->created_at->format('H:i') }}
                            </p>
                        </div>
                    </div>

                    {{-- KEPERLUAN --}}
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-2xl p-3.5 border border-slate-100 dark:border-slate-700 mb-4 min-h-[100px] flex flex-col">
                        <p class="text-[9px] uppercase font-black tracking-widest text-indigo-500 dark:text-indigo-400 mb-2">
                            Keperluan
                        </p>
                        <div class="mb-2">
                            <p class="text-[8px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest">
                                Jenis
                            </p>
                            <p class="text-xs font-bold text-slate-700 dark:text-slate-300 italic">
                                {{ $k->keperluan_master->keterangan ?? '-' }}
                            </p>
                        </div>
                        @if(!empty($k->keperluan))
                            <div>
                                <p class="text-[8px] uppercase font-black text-slate-400 dark:text-slate-500 tracking-widest">
                                    Detail
                                </p>
                                <p class="text-xs font-medium text-slate-600 dark:text-slate-400 leading-relaxed italic line-clamp-2">
                                    "{{ $k->keperluan }}"
                                </p>
                            </div>
                        @endif
                    </div>

{{-- FOOTER BUTTONS --}}
<div class="mt-auto flex items-center justify-between gap-2 pt-3 border-t border-slate-100 dark:border-slate-700">
    @if ($user->role_id == 2)
        <div class="flex items-center gap-2 w-full">
            @if($isAntre)
                <div class="flex flex-row items-center gap-2 w-full">
                    {{-- TOMBOL MULAI --}}
                    <button type="button" onclick="bukaModalProses('{{ $k->nomor_kunjungan }}')"
                        class="flex-1 h-9 px-3 sm:px-4 flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-all shadow-sm active:scale-[0.98]">
                        <i class="fa-solid fa-play text-[10px] pl-0.5"></i>
                        <span class="text-[11px] font-black uppercase tracking-wider">Mulai</span>
                    </button>

                    {{-- TOMBOL TOLAK --}}
                    <button type="button" onclick="bukaModalTolak('{{ $k->id }}')"
                        class="flex-1 h-9 px-3 sm:px-4 flex items-center justify-center gap-1.5 bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 hover:bg-rose-500 dark:hover:bg-rose-600 hover:text-white dark:hover:text-white rounded-xl transition-all active:scale-[0.98]">
                        <i class="fa-solid fa-xmark text-xs"></i>
                        <span class="text-[11px] font-black uppercase tracking-wider">Tolak</span>
                    </button>
                </div>
            @elseif($isDiproses)
                {{-- TOMBOL SELESAI --}}
                <form id="form-selesai-{{ $k->id }}" action="{{ route('kunjungan.selesai', $k->id) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="button" onclick="konfirmasiSelesai('{{ $k->id }}', '{{ $k->nomor_kunjungan }}')" title="Selesaikan Antrean"
                        class="w-9 h-9 flex items-center justify-center bg-emerald-500 hover:bg-emerald-600 text-white rounded-full transition-all shadow-sm active:scale-95 shrink-0">
                        <i class="fa-solid fa-check text-sm"></i>
                    </button>
                </form>
            @elseif($isSelesai)
                {{-- BADGE SELESAI --}}
                <div title="Selesai" class="w-9 h-9 flex items-center justify-center bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-600 dark:text-emerald-400 rounded-full shrink-0">
                    <i class="fa-solid fa-check-double text-xs"></i>
                </div>
            @endif
        </div>

        {{-- FITUR TAMBAHAN SETELAH SELESAI (KIRIM EMAIL & TERUSKAN) --}}
        <div class="flex items-center gap-2">
            @if($isSelesai)
                <form action="{{ route('kunjungan.kirim-email', ['id' => $k->id]) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" title="Kirim Email" class="w-9 h-9 flex items-center justify-center bg-sky-50 dark:bg-sky-950/40 hover:bg-sky-100 dark:hover:bg-sky-900 text-sky-600 dark:text-sky-400 rounded-full transition-all active:scale-95 shrink-0">
                        <i class="fa-regular fa-envelope text-sm"></i>
                    </button>
                </form>

                <form action="{{ route('kunjungan.tanggapan', $k->id) }}" method="POST" class="inline-block">
                    @csrf
                    <button type="submit" title="Teruskan ke Pimpinan" class="w-9 h-9 flex items-center justify-center bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900 text-amber-600 dark:text-amber-400 rounded-full transition-all active:scale-95 shrink-0">
                        <i class="fa-solid fa-share-nodes text-sm"></i>
                    </button>
                </form>
            @endif
        </div>
    @else
        {{-- TAMPILAN READ-ONLY UNTUK SUPER ADMIN / LAINNYA (DENGAN TAMPILAN TAILWIND) --}}
        <div class="flex items-center justify-center w-full py-1">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200/60 dark:border-slate-700/50 w-full justify-center">
                <i class="fa-solid fa-eye text-[11px]"></i>
                (Read-Only)
            </span>
        </div>
    @endif
</div>
                </div>
            </div>

        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2.5rem] py-20 px-6 text-center">
                <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 dark:bg-slate-700 flex items-center justify-center text-slate-300 dark:text-slate-500 text-3xl mb-5">
                    <i class="fa-solid fa-inbox"></i>
                </div>
                <h3 class="text-xl font-black text-slate-500 dark:text-slate-400">Belum Ada Antrean</h3>
                <p class="text-slate-400 dark:text-slate-500 mt-2 text-sm max-w-xs mx-auto">
                    Antrean baru yang masuk akan muncul secara otomatis di sini.
                </p>
            </div>
        @endforelse

    </div>

    {{-- TITLE SECTION ULASAN TERBARU --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-14 mb-6 w-full">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-8 bg-amber-500 rounded-full"></div>
            <h3 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">
                Ulasan Terbaru
            </h3>
        </div>

        @if(($data_ulasan ?? collect())->count() > 3)
            <a href="{{ route('dashboard.ulasan') }}" class="w-full sm:w-auto px-6 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-black text-xs uppercase tracking-widest rounded-2xl shadow-sm transition-all flex items-center justify-center gap-2 shrink-0">
                <span>Lihat semua ulasan</span>
                <i class="fa-solid fa-arrow-right text-xs"></i>
            </a>
        @endif
    </div>
{{-- LIST 3 ULASAN TERBARU --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        @forelse(($data_ulasan ?? collect())->take(3) as $item)
            @php
                $detail = $item->survey->detail ?? null;
                $avgRating = $detail ? ($detail->p1 + $detail->p2 + $detail->p3 + $detail->p4 + $detail->p5) / 5 : 0;
                $ratingBulat = round($avgRating);
            @endphp
            <div class="bg-white dark:bg-slate-800 p-6 sm:p-8 rounded-[2rem] border border-slate-100 dark:border-slate-700/60 shadow-sm hover:shadow-md transition-all flex flex-col justify-between h-full group">
                <div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex gap-1 text-amber-400">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fa-solid fa-star text-xs {{ $i <= $ratingBulat ? '' : 'text-slate-100 dark:text-slate-700' }}"></i>
                            @endfor
                        </div>
                        <!-- MENGUBAH ASAL INSTANSI MENJADI DIRAHASIAKAN -->
                        <span class="bg-slate-50 dark:bg-slate-900 text-slate-400 dark:text-slate-500 text-[9px] font-black px-3 py-1 rounded-full uppercase tracking-widest border border-slate-100 dark:border-slate-700 group-hover:bg-indigo-50 dark:group-hover:bg-indigo-950/40 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 group-hover:border-indigo-100 dark:group-hover:border-indigo-900 transition-colors">
                            DIRAHASIAKAN
                        </span>
                    </div>
                    <p class="text-slate-700 dark:text-slate-300 font-bold text-sm sm:text-base leading-relaxed mb-6 text-left italic">
                        "{!! $item->survey->kritik_saran ?? 'Hanya memberikan rating bintang.' !!}"
                    </p>
                </div>
                <div class="pt-4 border-t border-slate-50 dark:border-slate-700 flex flex-col text-left">
                    <!-- MENGUBAH NAMA LENGKAP MENJADI ANONIM -->
                    <span class="text-slate-900 dark:text-white font-black text-sm">
                        Anonim
                    </span>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                        <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-wider">
                            {{ $item->created_at->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-800 border-2 border-dashed border-slate-200 dark:border-slate-700 rounded-[2rem] py-10 px-6 text-center">
                <p class="text-slate-400 dark:text-slate-500 font-bold text-sm">Belum ada ulasan yang masuk.</p>
            </div>
        @endforelse
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
            <button onclick="downloadLaporan('xlsx')"
                class="flex items-center justify-center gap-2.5 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500 dark:hover:bg-emerald-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
                <i class="fa-regular fa-file-excel text-base"></i> Excel
            </button>
            <button onclick="downloadLaporan('pdf')"
                class="flex items-center justify-center gap-2.5 bg-rose-600 hover:bg-rose-700 dark:bg-rose-500 dark:hover:bg-rose-600 text-white py-4 rounded-2xl font-black text-xs uppercase tracking-widest transition-all shadow-xl dark:shadow-none hover:scale-[1.02]">
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

{{-- MODAL TOLAK --}}
<div id="modalTolak" class="fixed inset-0 z-[999] hidden items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[2rem] p-6 shadow-2xl border dark:border-slate-800">
        <div class="mb-5">
            <h2 class="text-xl font-black text-slate-900 dark:text-white">Tolak Antrean</h2>
            <p class="text-sm text-slate-400 dark:text-slate-500 mt-1">Wajib isi alasan penolakan</p>
        </div>
       <form id="formTolak" method="POST" action="">
    @csrf
    <textarea name="alasan_tolak" required
        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl p-4 text-sm font-medium text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-rose-100 dark:focus:ring-rose-950 outline-none"
        placeholder="Contoh: Dokumen tidak lengkap / data tidak valid"></textarea>
    <div class="flex gap-3 mt-5">
        <button type="button" onclick="tutupModalTolak()" class="flex-1 py-3 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-black text-xs uppercase">Batal</button>
        <button type="submit" class="flex-1 py-3 rounded-2xl bg-rose-600 text-white font-black text-xs uppercase shadow-lg dark:shadow-none">Kirim Penolakan</button>
    </div>
</form>
    </div>
</div>

{{-- MODAL SLA --}}
<div id="modalProsesSLA" class="fixed inset-0 z-[999] hidden items-center justify-center p-4 bg-slate-900/60 dark:bg-slate-950/80 backdrop-blur-sm transition-opacity duration-300">
    <div class="bg-white dark:bg-slate-900 w-full max-w-md rounded-[2.5rem] p-6 sm:p-10 shadow-2xl border dark:border-slate-800 transform transition-all scale-95 opacity-0 relative overflow-hidden" id="modalContentSLA">

        <div id="loadingOverlaySLA" class="absolute inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-[2px] z-50 flex flex-col items-center justify-center hidden transition-all duration-300">
            <div class="w-12 h-12 border-4 border-indigo-200 border-t-indigo-600 rounded-full animate-spin mb-4"></div>
            <p class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest animate-pulse">Memproses...</p>
        </div>

        <div class="text-center mb-6">
            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-3xl bg-indigo-100 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl sm:text-3xl mx-auto mb-5 shadow-inner">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white tracking-tight">Estimasi Layanan</h2>
            <p class="text-slate-400 dark:text-slate-500 text-xs sm:text-sm mt-2 font-medium">Tentukan waktu pelayanan secara realistis</p>
        </div>

        <div class="mb-6 p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/50">
            <p class="text-[10px] uppercase font-black tracking-widest text-amber-600 dark:text-amber-400 mb-2">Perhatian</p>
            <p class="text-xs text-amber-700 dark:text-amber-300 font-semibold leading-relaxed">
                Estimasi hanya bisa diinput <b>1 kali</b>. Pastikan sudah sesuai dengan <b>jenis keperluan</b> dan perkiraan waktu pengerjaan layanan.
            </p>
        </div>

        <form id="formSLA" method="POST">
            @csrf
            <div class="grid grid-cols-1 gap-5 mb-8">
                <div class="space-y-2">
                    <label class="text-[10px] uppercase font-black text-slate-400 dark:text-slate-500 ml-2 tracking-widest">Durasi Pelayanan</label>
                    <input type="number" id="inputEstimasi" name="estimasi_sla" required
                        class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-4 font-bold text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none transition-all placeholder:text-slate-300 dark:placeholder:text-slate-700"
                        placeholder="Contoh: 15">
                </div>
                <div class="space-y-2">
                    <label class="text-[10px] uppercase font-black text-slate-400 dark:text-slate-500 ml-2 tracking-widest">Satuan Waktu</label>
                    <div class="relative">
                        <select id="selectSatuan" name="satuan_sla" class="w-full bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl px-5 py-4 font-bold text-slate-700 dark:text-slate-300 focus:ring-4 focus:ring-indigo-100 dark:focus:ring-indigo-950 outline-none appearance-none transition-all">
                            <option value="Menit" class="dark:bg-slate-900">Menit</option>
                            <option value="Hari" class="dark:bg-slate-900">Hari</option>
                        </select>
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
                <button type="button" id="btnKembali" onclick="tutupModal()" class="order-2 sm:order-1 flex-1 py-4 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-black uppercase text-[11px] tracking-widest hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">Kembali</button>
                <button type="submit" id="btnSubmitSLA" class="order-1 sm:order-2 flex-1 py-4 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase text-[11px] tracking-widest shadow-lg dark:shadow-none transition-all flex items-center justify-center gap-2">
                    <span id="btnText">Mulai Sekarang</span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JAVASCRIPT LOGIC --}}
<script>
let isModalOpen = false;

    function openExportModal() {
        document.getElementById('exportStartDate').value = '';
        document.getElementById('exportEndDate').value = '';

        const modal = document.getElementById('exportModal');
        const content = document.getElementById('modalContentExport');

        // KUNCI 1: Ubah jadi true agar auto-refresh STOP saat modal terbuka
        isModalOpen = true;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }

    function closeExportModal() {
        const modal = document.getElementById('exportModal');
        const content = document.getElementById('modalContentExport');

        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // KUNCI 2: Jika modal ditutup manual tanpa download, kembalikan ke false agar refresh JALAN LAGI
            isModalOpen = false;
        }, 200);
    }

    // =========================================================================
    // MODIFIKASI PREMIUM: DOWNLOAD LAPORAN DENGAN BLOB & LOADING MODAL SINKRON
    // =========================================================================
    function downloadLaporan(type) {
        const startDate = document.getElementById('exportStartDate').value;
        const endDate = document.getElementById('exportEndDate').value;

        if (!startDate || !endDate) {
            alert('Silakan tentukan rentang tanggal awal dan akhir terlebih dahulu.');
            return;
        }

        const urlParams = new URLSearchParams(window.location.search);
        const prodiId = urlParams.get('prodi_id') || '';

        // 1. TAMPILKAN POP-UP LOADING
        const loadingModal = document.getElementById('loading-modal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }

        // 2. TUTUP MODAL INPUT TANGGAL
        closeExportModal();

        // KUNCI 3: Karena closeExportModal() di atas mengubah status jadi false,
        // kita paksa kunci lagi ke TRUE agar auto-refresh TETAP STOP selama pop-up loading berputar
        isModalOpen = true;

        // 3. JALANKAN DOWNLOAD (KODE ASLI ANDA TIDAK DIUBAH)
        window.location = '/laporan/pengunjung' +
                          '?type=' + type +
                          '&start_date=' + startDate +
                          '&end_date=' + endDate +
                          '&prodi_id=' + prodiId;

        // 4. TUTUP OTOMATIS POP-UP LOADING SETELAH FORMAT FILE DILEMPAR KE BROWSER
        setTimeout(function() {
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }

            // KUNCI 4: Setelah 15 detik berlalu dan loading hilang, kembalikan ke false agar refresh KEMBALI NORMAL
            isModalOpen = false;
        }, 15000); // Menutup loading dalam 15 detik setelah download dipicu
    }

    function bukaModalProses(nomor) {
        const modal = document.getElementById('modalProsesSLA');
        const content = document.getElementById('modalContentSLA');
        const form = document.getElementById('formSLA');

        // DIKUNCI KE TRUE: Agar dashboard tidak tiba-tiba auto-refresh saat Anda sedang mengetik estimasi waktu
        isModalOpen = true;

        form.action = `/dashboard/mulai-proses/${nomor}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        setTimeout(() => {
            if (content) {
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }
        }, 10);
    }

    function tutupModal() {
        const modal = document.getElementById('modalProsesSLA');
        const content = document.getElementById('modalContentSLA');

        if (content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
        }

        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');

            // DIKEMBALIKAN KE FALSE: Agar auto-refresh aktif kembali setelah modal ditutup manual
            isModalOpen = false;
        }, 200);
    }

    function filterProdi(val) {
        const url = new URL(window.location.href);
        if (val) {
            url.searchParams.set('prodi_id', val);
        } else {
            url.searchParams.delete('prodi_id');
        }
        window.location.href = url.toString();
    }

    function bukaModalTolak(id) {
        const modal = document.getElementById('modalTolak');
        const form = document.getElementById('formTolak');

        // KUNCI MODAL TOLAK: Stop auto-refresh saat modal penolakan dibuka
        isModalOpen = true;

        // PERBAIKAN: Diubah dari /tolak-antrean/ menjadi /tolak/ agar sesuai dengan Route Laravel Anda
        form.action = `/dashboard/tolak/${id}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function tutupModalTolak() {
        const modal = document.getElementById('modalTolak');
        modal.classList.add('hidden');
        modal.classList.remove('flex');

        isModalOpen = false;
    }

    // ==========================================
// UTILITY: POP-UP LOADING GLOBAL & LOCK (Bisa di-blur)
// ==========================================
function showGlobalLoading(pesanText = "Sedang memproses data, mohon tunggu...") {
    const isDarkMode = document.documentElement.classList.contains('dark');

    window.onclick = null;

    Swal.fire({
        title: 'Memproses Data',
        text: pesanText,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showConfirmButton: false,
        background: isDarkMode ? '#1e293b' : '#ffffff',
        color: isDarkMode ? '#f8fafc' : '#1f2937',

        // EFEK BLUR DI LATAR BELAKANG (Backdrop Blur)
        backdrop: `
            rgba(15, 23, 42, 0.3)
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        `,

        customClass: {
            popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700 p-8',
            title: 'font-black text-xl tracking-tight',
            htmlContainer: 'text-sm text-gray-500 dark:text-gray-400 mt-2'
        },
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// ==========================================
// KONFIRMASI SELESAI (Bisa di-blur)
// ==========================================
function konfirmasiSelesai(id, nomor) {
    const form = document.getElementById(`form-selesai-${id}`);
    if (!form) return;

    const isDarkMode = document.documentElement.classList.contains('dark');

    Swal.fire({
        title: 'Selesaikan Layanan?',
        text: `Apakah Anda yakin ingin menyelesaikan antrean nomor ${nomor}? Pastikan pelayanan telah selesai dikerjakan.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Selesai',
        cancelButtonText: 'Batal',

        background: isDarkMode ? '#1e293b' : '#ffffff',
        color: isDarkMode ? '#f8fafc' : '#1f2937',
        confirmButtonColor: '#10b981',
        cancelButtonColor: isDarkMode ? '#475569' : '#94a3b8',

        // EFEK BLUR DI LATAR BELAKANG (Backdrop Blur)
        backdrop: `
            rgba(15, 23, 42, 0.2)
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
        `,

        customClass: {
            popup: 'rounded-[2rem] shadow-2xl border border-gray-100 dark:border-slate-700 p-6',
            title: 'font-black text-xl tracking-tight',
            htmlContainer: 'text-sm text-gray-500 dark:text-gray-400 mt-2',
            confirmButton: 'rounded-xl font-bold text-sm px-5 py-2.5',
            cancelButton: 'rounded-xl font-bold text-sm px-5 py-2.5'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Panggil fungsi pop-up loading global yang sudah di-blur di atas
            if (typeof showGlobalLoading === 'function') {
                showGlobalLoading("Sedang memperbarui status layanan menjadi selesai...");
            }
            form.submit();
        }
    });
}

    // ====================================================================================
    // PERBAIKAN AMAN: MERAPIKAN STRUKTUR SUBMIT FORM ESTIMASI TANPA MENGHAPUS LOGIKA KODE
    // ====================================================================================
    document.getElementById('formSLA').addEventListener('submit', function(e) {
        const overlay = document.getElementById('loadingOverlaySLA');
        const btnKembali = document.getElementById('btnKembali');
        const btnSubmit = document.getElementById('btnSubmitSLA');
        const btnText = document.getElementById('btnText');
        const inputEstimasi = document.getElementById('inputEstimasi');
        const selectSatuan = document.getElementById('selectSatuan');

        if (overlay) {
            overlay.classList.remove('hidden');
        }

        // Matikan tombol saja agar user tidak melakukan klik ganda (double-submit)
        btnKembali.disabled = true;
        btnSubmit.disabled = true;

        btnKembali.classList.add('opacity-50', 'cursor-not-allowed');
        btnSubmit.classList.add('opacity-80', 'cursor-not-allowed');

        // Perbaikan Utama: Baris inputEstimasi.disabled & selectSatuan.disabled DIHAPUS dari sini,
        // agar data durasi yang Anda masukkan di layar dikirim 100% utuh ke Laravel Anda.
    });

    // Fungsi bawaan Anda dikeluarkan secara rapi agar tidak merusak penutupan tag script
    function matikanLoading() {
        const overlay = document.getElementById('loadingOverlaySLA');
        const btnKembali = document.getElementById('btnKembali');
        const btnSubmit = document.getElementById('btnSubmitSLA');
        const inputEstimasi = document.getElementById('inputEstimasi');
        const selectSatuan = document.getElementById('selectSatuan');

        if (overlay) overlay.classList.add('hidden');
        btnKembali.disabled = false;
        btnSubmit.disabled = false;
        inputEstimasi.disabled = false;
        selectSatuan.disabled = false;
        btnKembali.classList.remove('opacity-50', 'cursor-not-allowed');
        btnSubmit.classList.remove('opacity-80', 'cursor-not-allowed');
    }
</script>
@endsection
