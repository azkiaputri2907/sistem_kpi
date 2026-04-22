<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Layanan - {{ $kunjungan->nomor_kunjungan }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #F8FAFC; }
        .gradient-bg { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .gradient-red { background: linear-gradient(135deg, #e11d48 0%, #be123c 100%); }
        .timeline-line { position: absolute; left: 11px; top: 24px; bottom: 0; width: 2px; background-color: #F1F5F9; }
        .animate-bounce-slow { animation: bounce 3s infinite; }
        @keyframes bounce { 0%, 100% { transform: translateY(-5%); } 50% { transform: translateY(0); } }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-6 bg-[#F1F5F9]">

    <div class="max-w-md w-full space-y-6">

        {{-- CARD UTAMA --}}
        <div class="bg-white rounded-[2.5rem] overflow-hidden shadow-sm border border-slate-100">
            <div class="{{ $kunjungan->status_layanan == 'Ditolak' ? 'gradient-red' : 'gradient-bg' }} p-8 text-white text-center relative overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-white/10 rounded-full -mr-12 -mt-12 blur-xl"></div>

                <div class="w-16 h-16 bg-white/20 rounded-full mx-auto flex items-center justify-center mb-4 backdrop-blur-md border border-white/30 shadow-inner">
                    @if($kunjungan->status_layanan == 'Selesai')
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    @elseif($kunjungan->status_layanan == 'Ditolak')
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    @else
                        <svg class="w-8 h-8 text-white animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @endif
                </div>

                <h1 class="text-3xl font-extrabold tracking-tight mb-2 uppercase">{{ $kunjungan->status_layanan }}</h1>
                <p class="text-white/70 text-[11px] font-medium mb-6 tracking-wide">Nomor Antrean Anda</p>

                <div class="bg-white/10 backdrop-blur-md py-4 rounded-3xl border border-white/20">
                    <p class="text-[3.5rem] font-black tracking-tighter leading-none mb-1">{{ $kunjungan->nomor_kunjungan }}</p>
                </div>

                {{-- INFO ESTIMASI SLA --}}
                @if($kunjungan->status_layanan == 'Diproses' && $kunjungan->estimasi_sla)
                    <div class="mt-6 p-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 animate-bounce-slow">
                        <p class="text-[10px] font-black uppercase tracking-widest text-white/80 mb-1">Estimasi Waktu Tunggu</p>
                        <div class="flex items-center justify-center gap-2">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xl font-black text-white">
                                {{ $kunjungan->estimasi_sla }} {{ $kunjungan->satuan_sla }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
                        {{-- DURASI PELAYANAN --}}
                @if($kunjungan->status_layanan == 'Selesai')
                <div class="p-6 bg-emerald-50/40 border-b border-emerald-100 flex items-center justify-between">
                    <div>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider">Durasi Pelayanan</p>
                        <p class="text-xl font-extrabold text-emerald-700">
                            {{-- Menggunakan number_format untuk memastikan 2 digit di belakang koma --}}
                            {{ number_format($kunjungan->created_at->diffInMinutes($kunjungan->waktu_selesai_layanan ?? $kunjungan->updated_at), 2) }} Menit
                        </p>
                    </div>
                    <div class="px-4 py-1.5 bg-emerald-500 text-white text-[10px] font-black rounded-full uppercase tracking-tighter shadow-sm">
                        Selesai
                    </div>
                </div>


                {{-- CARD DOWNLOAD FILE PDF (Bukti Keperluan Terpenuhi) --}}
                @if($kunjungan->file_surat)
                <div class="p-6 bg-white border-b border-slate-50 flex flex-col items-center text-center">
                    <div class="w-12 h-12 bg-emerald-100 rounded-2xl flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight">Dokumen Hasil Layanan</h3>
                    <p class="text-[11px] text-slate-400 mb-4 px-4 leading-relaxed">Dokumen balasan Anda telah tersedia. Silakan klik tombol di bawah untuk mengunduh.</p>

                    <a href="{{ asset('storage/surat/' . $kunjungan->file_surat) }}"
                       target="_blank"
                       class="w-full flex items-center justify-center gap-2 py-3 bg-emerald-500 text-white text-xs font-bold rounded-xl hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Unduh Berkas (PDF)
                    </a>
                </div>
                @endif
            @endif

            <div class="px-8 py-6 flex justify-between border-b border-slate-50 bg-slate-50/30">
                <div>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Pengunjung</p>
                    <p class="text-sm font-bold text-slate-700">{{ $kunjungan->pengunjung->nama_lengkap }}</p>
                </div>
                <div class="text-right">
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Waktu Terbit</p>
                    <p class="text-sm font-bold text-slate-700">{{ $kunjungan->created_at->format('H:i') }} WIB</p>
                </div>
            </div>
        </div>

      {{-- TANGGAPAN PIMPINAN (Hanya muncul jika sudah SELESAI/DITOLAK dan status pimpinan bukan Menunggu) --}}
@if(in_array($kunjungan->status_layanan, ['Selesai', 'Ditolak']) && $kunjungan->status_pimpinan != 'Menunggu')
<div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
    <div class="flex items-center gap-2 mb-6">
        <div class="w-2 h-5 {{ $kunjungan->status_pimpinan == 'Disetujui' ? 'bg-emerald-500' : 'bg-rose-500' }} rounded-full"></div>
        <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Tanggapan Pimpinan</h2>
    </div>

    <div class="p-5 rounded-3xl {{ $kunjungan->status_pimpinan == 'Disetujui' ? 'bg-emerald-50 border border-emerald-100' : 'bg-rose-50 border border-rose-100' }}">
        <div class="flex items-center gap-3 mb-3">
            <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tighter {{ $kunjungan->status_pimpinan == 'Disetujui' ? 'bg-emerald-500 text-white' : 'bg-rose-500 text-white' }}">
                {{ $kunjungan->status_pimpinan }}
            </span>
        </div>

        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Catatan Pimpinan:</p>
        <p class="text-sm font-bold text-slate-700 italic">
            "{{ $kunjungan->catatan_pimpinan ?? 'Tidak ada catatan khusus.' }}"
        </p>
    </div>
</div>
@endif

        {{-- RIWAYAT STATUS --}}
        <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-slate-100">
            <div class="flex items-center gap-2 mb-8">
                <div class="w-2 h-5 bg-indigo-600 rounded-full"></div>
                <h2 class="text-sm font-black text-slate-800 uppercase tracking-widest">Riwayat Status</h2>
            </div>

            <div class="relative space-y-10">
                <div class="timeline-line"></div>

                {{-- STATUS 3: SELESAI --}}
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white z-10 flex items-center justify-center
                        {{ $kunjungan->status_layanan == 'Selesai' ? 'bg-emerald-500 shadow-lg shadow-emerald-100' : 'bg-slate-200' }}">
                        @if($kunjungan->status_layanan == 'Selesai')
                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="4"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        @endif
                    </div>
                    <h4 class="text-sm font-bold {{ $kunjungan->status_layanan == 'Selesai' ? 'text-slate-900' : 'text-slate-300' }}">Layanan Selesai</h4>
                    <p class="text-[11px] font-medium text-slate-400">
                        {{ $kunjungan->status_layanan == 'Selesai' ? 'Tuntas pada ' . ($kunjungan->waktu_selesai_layanan ? Carbon\Carbon::parse($kunjungan->waktu_selesai_layanan)->format('H:i') : $kunjungan->updated_at->format('H:i')) . ' WIB' : 'Menunggu penyelesaian' }}
                    </p>
                </div>

                {{-- STATUS 2: DIPROSES --}}
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white z-10 flex items-center justify-center
                        {{ in_array($kunjungan->status_layanan, ['Diproses', 'Selesai']) ? 'bg-blue-500 shadow-lg shadow-blue-100' : 'bg-slate-200' }}">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-sm font-bold {{ in_array($kunjungan->status_layanan, ['Diproses', 'Selesai']) ? 'text-slate-900' : 'text-slate-300' }}">Sedang Diproses</h4>
                    @if($kunjungan->status_layanan == 'Diproses')
                        <p class="text-[11px] font-bold text-indigo-600">Petugas sedang melayani Anda</p>
                    @else
                        <p class="text-[11px] font-medium text-slate-400">Petugas memproses keperluan Anda</p>
                    @endif
                </div>

                {{-- STATUS 1: TERDAFTAR --}}
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-6 h-6 rounded-full ring-4 ring-white z-10 bg-amber-400 shadow-lg shadow-amber-100 flex items-center justify-center">
                        <div class="w-1.5 h-1.5 bg-white rounded-full"></div>
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Tiket Terdaftar</h4>
                    <p class="text-[11px] font-medium text-amber-600 font-bold">Pukul {{ $kunjungan->created_at->format('H:i') }} WIB</p>
                </div>
            </div>
        </div>

        {{-- AKSI --}}
        <div class="pt-2 space-y-4">
            @if($kunjungan->status_layanan == 'Selesai')
                <a href="{{ route('survey.form', $kunjungan->nomor_kunjungan) }}" class="w-full flex items-center justify-center py-5 bg-indigo-600 text-white font-extrabold rounded-[2rem] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all active:scale-95">
                    Beri Ulasan Layanan ⭐
                </a>
            @endif

            <a href="{{ route('landing') }}" class="w-full flex items-center justify-center py-5 bg-slate-900 text-white font-extrabold rounded-[2rem] shadow-xl hover:bg-black transition-all active:scale-95 gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <p class="text-center text-[10px] font-black text-slate-300 uppercase tracking-[0.6em]">Digital Gate System</p>
    </div>

    {{-- AUTO RELOAD JIKA BELUM SELESAI --}}
    @if(!in_array($kunjungan->status_layanan, ['Selesai', 'Ditolak']))
    <script>
        setTimeout(function(){ window.location.reload(1); }, 15000);
    </script>
    @endif

</body>
</html>
