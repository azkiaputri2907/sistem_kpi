<!DOCTYPE html>
<html>
<head>
    <title>Notifikasi Kunjungan Baru</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
        <div style="background-color: #2d3748; padding: 20px; text-align: center;">
            <h2 style="color: #ffffff; margin: 0;">Layanan Publik Elektro</h2>
        </div>

        <div style="padding: 30px;">
            <p>Halo, <strong>Bapak/Ibu Pimpinan</strong></p>
            <p>Terdapat antrean kunjungan baru yang diteruskan kepada Anda. Berikut adalah rinciannya:</p>

            <div style="background-color: #f7fafc; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 5px 0; width: 40%;"><strong>Nomor Antrean</strong></td>
                        <td>: {{ $kunjungan->nomor_kunjungan }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Nama Pengunjung</strong></td>
                        <td>: {{ $kunjungan->pengunjung->nama_lengkap }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Keperluan</strong></td>
                        <td>: {{ $kunjungan->keperluan }}</td>
                    </tr>
                </table>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ url('/login') }}" style="background-color: #4a5568; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                    Login ke Dashboard
                </a>
            </p>

            <p style="font-size: 0.8em; color: #718096; text-align: center; margin-top: 20px;">
                Silakan masuk ke sistem untuk memproses layanan ini.
            </p>
        </div>

        <div style="background-color: #edf2f7; padding: 15px; text-align: center; font-size: 0.8em; color: #a0aec0;">
            &copy; {{ date('Y') }} Jurusan Teknik Elektro - Semua Hak Dilindungi.
        </div>
    </div>
</body>
</html>
