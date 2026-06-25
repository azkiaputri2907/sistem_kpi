<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi Kunjungan Baru</title>
    <style>
        /* CSS Reset untuk Client Email Mobile */
        html, body { margin: 0 auto !important; padding: 0 !important; height: 100% !important; width: 100% !important; background-color: #f1f5f9; }
        * { -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%; }
        div[style*="margin: 16px 0"] { margin: 0 !important; }
        table, td { mso-table-lspace: 0pt !important; mso-table-rspace: 0pt !important; }
        table { border-spacing: 0 !important; border-collapse: collapse !important; table-layout: fixed !important; margin: 0 auto !important; }
        img { -ms-interpolation-mode: bicubic; max-width: 100%; height: auto; }
        a { text-decoration: none; }

        /* RESPONSIVE BREAKPOINTS (Handphone & Tablet) */
        @media screen and (max-width: 600px) {
            .email-container { width: 100% !important; margin: auto !important; border-radius: 0px !important; }
            .content-padding { padding: 24px !important; }
            .header-padding { padding: 32px 24px !important; }
            .info-box { padding: 16px !important; border-radius: 16px !important; }
            .mobile-title { font-size: 20px !important; }
            .mobile-btn { display: block !important; padding: 16px 20px !important; }
        }
    </style>
</head>
<body style="font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif; background-color: #f1f5f9; color: #1e293b; margin: 0; padding: 0; width: 100%;">

    <div style="display: block; height: 40px;" class="content-padding"></div>

    <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" max-width="560" class="email-container" style="max-width: 560px; margin: 0 auto; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.05); overflow: hidden;">

        <tr>
            <td class="header-padding" style="padding: 40px; background-color: #0b192c; background-image: linear-gradient(135deg, rgba(11, 25, 44, 0.92), rgba(26, 43, 76, 0.85)), url('{{ asset('img/bg-poliban.jpg') }}'); background-size: cover; background-position: center; text-align: left; border-bottom: 4px solid #facc15;">
                <p style="color: #ef4444; font-size: 11px; font-weight: 900; text-transform: uppercase; letter-spacing: 0.25em; margin: 0 0 6px 0;">Sistem Notifikasi</p>
                <h2 class="mobile-title" style="color: #ffffff; font-size: 24px; font-weight: 800; letter-spacing: -0.025em; margin: 0;">Layanan Publik Elektro</h2>
            </td>
        </tr>

        <tr>
            <td class="content-padding" style="padding: 40px; background-color: #ffffff;">
                <p style="font-size: 16px; margin: 0 0 10px 0; color: #0f172a; font-weight: 500;">
                    Halo, <strong style="font-weight: 800; color: #1e3a8a;">Bapak/Ibu Pimpinan</strong>
                </p>
                <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 32px 0; font-weight: 400;">
                    Terdapat permintaan persetujuan atau konfirmasi antrean kunjungan baru yang diteruskan kepada Anda. Berikut rincian datanya:
                </p>

                <div class="info-box" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #1e3a8a; border-radius: 16px; padding: 24px; margin-bottom: 32px;">

                    <table width="100%" role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin-bottom: 16px;">
                        <tr>
                            <td style="padding: 0; vertical-align: top;">
                                <span style="font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.15em; color: #64748b; display: block; margin-bottom: 4px;">Nama Pengunjung</span>
                                <span style="font-size: 16px; font-weight: 800; color: #0f172a; display: block;">{{ $kunjungan->pengunjung->nama_lengkap ?? 'Umum' }}</span>
                            </td>
                            <td style="padding: 0; text-align: right; vertical-align: top;" width="130">
                                <span style="background-color: #ef4444; color: #ffffff; padding: 6px 12px; border-radius: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block;">
                                    Antrean: {{ $kunjungan->nomor_kunjungan }}
                                </span>
                            </td>
                        </tr>
                    </table>

                    <div style="border-top: 1px solid #e2e8f0; padding-top: 14px; margin-bottom: 14px;">
                        <p style="font-size: 10px; color: #64748b; font-weight: 800; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.05em;">Asal Instansi / Universitas:</p>
                        <p style="font-size: 14px; color: #0f172a; font-weight: 700; margin: 0 0 12px 0;">
                            {{ $kunjungan->pengunjung->instansi }}
                        </p>

                        @if(!empty($kunjungan->nama_prodi))
                        <p style="font-size: 10px; color: #64748b; font-weight: 800; margin: 0 0 4px 0; text-transform: uppercase; letter-spacing: 0.05em;">Program Studi Terkait:</p>
                        <p style="font-size: 13px; color: #1e3a8a; font-weight: 700; margin: 0;">
                            {{ $kunjungan->nama_prodi }}
                        </p>
                        @endif
                    </div>

                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-top: 12px;">
                        <div style="margin-bottom: 10px;">
                            <p style="font-size: 10px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 2px 0;">Keperluan / Layanan:</p>
                            <p style="font-size: 14px; color: #1e3a8a; font-weight: 700; margin: 0;">
                                {{ $kunjungan->nama_keperluan_utama }}
                            </p>
                        </div>
                        <div style="border-top: 1px solid #f1f5f9; padding-top: 10px;">
                            <p style="font-size: 10px; color: #94a3b8; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 2px 0;">Keterangan Detail:</p>
                            <p style="font-size: 13px; color: #475569; font-style: italic; font-weight: 500; margin: 0; line-height: 1.5;">
                                "{{ $kunjungan->keperluan_detail }}"
                            </p>
                        </div>
                    </div>
                </div>

                <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;">
                    <tr>
                        <td align="center" style="border-radius: 12px; background-color: #0b192c;">
                            <a href="{{ url('/dashboard/pimpinan/konfirmasi') }}" class="mobile-btn" style="background-color: #0b192c; border: 1px solid #0b192c; color: #ffffff; padding: 16px 36px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.07em; border-radius: 12px; display: inline-block; transition: all 0.2s ease;">
                                Beri Tanggapan Sekarang
                            </a>
                        </td>
                    </tr>
                </table>

                <p style="font-size: 12px; color: #94a3b8; font-weight: 600; text-align: center; margin: 28px 0 0 0;">
                    Atau silakan masuk via akun Anda melalui tautan di atas.
                </p>
            </td>
        </tr>

        <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 24px; text-align: center; font-size: 11px; font-weight: 600; color: #94a3b8; letter-spacing: 0.025em;">
                &copy; {{ date('Y') }} Jurusan Teknik Elektro Poliban. All Rights Reserved.
            </td>
        </tr>
    </table>

    <div style="display: block; height: 40px;" class="content-padding"></div>

</body>
</html>
