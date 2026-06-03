<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Kunjungan Baru</title>
</head>
<body style="font-family: 'Segoe UI', system-ui, -apple-system, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 40px 20px; -webkit-font-smoothing: antialiased;">
    
    <div style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border: 1px solid #f1f5f9; border-radius: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -2px rgba(0, 0, 0, 0.05); overflow: hidden;">
        
        {{-- HEADER BRAND --}}
        <div style="background: linear-gradient(135deg, #4f46e5, #4338ca); padding: 32px 40px; text-align: left;">
            <p style="color: #c7d2fe; font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.25em; margin: 0 0 4px 0;">Sistem Notifikasi</p>
            <h2 style="color: #ffffff; font-size: 24px; font-weight: 900; tracking-tight: -0.025em; margin: 0;">Layanan Publik Elektro</h2>
        </div>

        {{-- KONTEN UTAMA --}}
        <div style="padding: 40px;">
            <p style="font-size: 16px; margin: 0 0 8px 0; color: #0f172a;">Halo, <strong style="font-weight: 800; color: #4f46e5;">Bapak/Ibu Pimpinan</strong></p>
            <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin: 0 0 32px 0; font-weight: 500;">Terdapat permintaan persetujuan atau konfirmasi antrean kunjungan baru yang baru saja diteruskan kepada Anda. Berikut rincian datanya:</p>

            {{-- KOTAK INFORMASI DETAIL PENGUNJUNG (GAYA SAMA SEPERTI MODAL APP) --}}
            <div style="background-color: #f5f3ff; border: 1px solid #e0e7ff; border-radius: 20px; padding: 24px; margin-bottom: 32px;">
                
                {{-- Bagian Atas: Label Pengunjung & Antrean --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 16px;">
                    <tr>
                        <td style="padding: 0; vertical-align: middle;">
                            <span style="font-size: 10px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.15em; color: #4f46e5; display: block; margin-bottom: 2px;">Pengunjung</span>
                            <span style="font-size: 16px; font-weight: 800; color: #1e293b; display: block;">{{ $kunjungan->pengunjung->nama_lengkap ?? 'Umum' }}</span>
                        </td>
                        <td style="padding: 0; text-align: right; vertical-align: middle;">
                            <span style="background-color: #4f46e5; color: #ffffff; padding: 6px 14px; border-radius: 12px; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block;">
                                Antrean: {{ $kunjungan->nomor_kunjungan }}
                            </span>
                        </td>
                    </tr>
                </table>

{{-- Detail Tambahan Instansi, Prodi & ID --}}
<div style="border-top: 1px dashed #e0e7ff; padding-top: 14px; margin-bottom: 14px;">
    <p style="font-size: 11px; color: #64748b; font-weight: 700; margin: 0 0 4px 0; text-transform: uppercase;">Asal Instansi / Universitas:</p>
    <p style="font-size: 14px; color: #1e293b; font-weight: 800; margin: 0 0 10px 0;">
        {{ $kunjungan->pengunjung->instansi }}
    </p>

    @if(!empty($kunjungan->nama_prodi))
    <p style="font-size: 11px; color: #64748b; font-weight: 700; margin: 0 0 4px 0; text-transform: uppercase;">Program Studi Terkait:</p>
    <p style="font-size: 13px; color: #4f46e5; font-weight: 800; margin: 0;">
        {{ $kunjungan->nama_prodi }}
    </p>
    @endif
</div>
                
                {{-- KOTAK INFORMASI KEPERLUAN UTAMA & DETAIL --}}
<div style="background-color: rgba(255, 255, 255, 0.7); border: 1px solid #e2e8f0; border-radius: 16px; padding: 16px; margin-top: 12px;">
    {{-- Baris 1: Kategori Keperluan Utama dari Master (Hasilnya: Legalisir Ijazah, Mengurus SP, dll) --}}
    <div style="margin-bottom: 12px;">
        <p style="font-size: 10px; color: #94a3b8; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 2px 0;">Keperluan / Layanan:</p>
        <p style="font-size: 14px; color: #4f46e5; font-weight: 800; margin: 0;">
            {{ $kunjungan->nama_keperluan_utama }}
        </p>
    </div>

    {{-- Baris 2: Detail Tambahan (Hasilnya: "1 bulan", "SP 4", atau "-") --}}
    <div style="border-top: 1px solid #f1f5f9; padding-top: 10px;">
        <p style="font-size: 10px; color: #94a3b8; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 2px 0;">Keterangan / Durasi Detail:</p>
        <p style="font-size: 13px; color: #475569; font-style: italic; font-weight: 600; margin: 0; line-height: 1.5;">
            "{{ $kunjungan->keperluan_detail }}"
        </p>
    </div>
</div>
            </div>

            {{-- TOMBOL CALL TO ACTION (CTA) --}}
            <div style="text-align: center; margin-top: 32px;">
                <a href="{{ url('/dashboard/pimpinan/konfirmasi') }}" style="background-color: #0f172a; color: #ffffff; padding: 16px 32px; text-decoration: none; border-radius: 16px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.1em; display: inline-block; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.15); transition: background-color 0.2s;">
                    Beri Tanggapan Sekarang
                </a>
            </div>

            <p style="font-size: 12px; color: #94a3b8; font-weight: 600; text-align: center; margin: 24px 0 0 0;">
                Atau silakan masuk via akun Anda melalui tautan di atas.
            </p>
        </div>

        {{-- FOOTER EMAIL --}}
        <div style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; padding: 24px; text-align: center; font-size: 11px; font-weight: 700; color: #94a3b8; letter-spacing: 0.025em;">
            &copy; {{ date('Y') }} Jurusan Teknik Elektro. All Rights Reserved.
        </div>
    </div>
    
</body>
</html>