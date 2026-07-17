<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class PimpinanKonfirmasiController extends Controller
{
    // =========================================================================
    // HELPER SPREADSHEET API
    // =========================================================================
    
    private function getApiUrl()
    {
        return env('GOOGLE_SCRIPT_URL');
    }

    private function readSheet($sheetName)
    {
        $response = Http::get($this->getApiUrl(), [
            'action' => 'read',
            'sheet'  => $sheetName
        ]);
        
        $data = $response->json('data') ?? [];
        // Mengubah array menjadi collection of objects agar syntax $item->kolom tetap jalan
        return collect(json_decode(json_encode($data), FALSE));
    }

    private function updateSheet($sheetName, $id, $data)
    {
        $data['id'] = $id;
        $response = Http::post($this->getApiUrl() . '?action=update&sheet=' . $sheetName, $data);
        return $response->json();
    }

    // =========================================================================
    // HELPER: MENGAMANKAN SESSION USER
    // =========================================================================
    private function getSessionUser()
    {
        $sessionUser = session('user');
        // Pastikan memaksa session menjadi Object agar tidak error "Attempt to read property on array"
        return $sessionUser ? (object) $sessionUser : null;
    }

    // =========================================================================
    // CORE LOGIC
    // =========================================================================

public function index()
    {
        // Ambil user dari helper manual (Sudah dibungkus Object)
        $user = $this->getSessionUser();

        if (!$user) {
            return redirect('/login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // 1. Ambil data mentah dari Spreadsheet
        $allKunjungan = $this->readSheet('kunjungan');
        $allPengunjung = $this->readSheet('pengunjung');
        // TAMBAHAN: Ambil data master keperluan
        $allKeperluan = $this->readSheet('master_keperluan');

        // 2. Filter data (Pengganti query WHERE di SQL)
        $data_konfirmasi = $allKunjungan->filter(function ($item) use ($user) {
            // Syarat utama: Harus yang sudah diteruskan (is_forwarded == 1)
            if ($item->is_forwarded != 1) return false;

            // Filter berdasarkan Role Pimpinan
            if ($user->role_id == 3) {
                // KAJUR: hanya lihat yang tujuannya 'kajur'
                return $item->tujuan_pimpinan == 'kajur';
            } 
            elseif ($user->role_id == 4) {
                // KAPRODI: lihat yang tujuannya 'kaprodi' DAN prodi_id sesuai
                return ($item->tujuan_pimpinan == 'kaprodi' && $item->prodi_id == $user->prodi_id);
            }

            return false;
        });

        // 3. Gabungkan dengan data pengunjung & keperluan
        $data_konfirmasi = $data_konfirmasi->map(function ($kunjungan) use ($allPengunjung, $allKeperluan) {
            $kunjungan->pengunjung = $allPengunjung->firstWhere('id', $kunjungan->pengunjung_id);
            
            // PENCARIAN KEPERLUAN UTAMA BERDASARKAN ID
            $master = $allKeperluan->firstWhere('id', $kunjungan->keperluan_id);
            $kunjungan->nama_keperluan_utama = $master ? $master->keterangan : 'Layanan Umum';

            return $kunjungan;
        });

        // 4. Urutkan berdasarkan waktu terbaru
        $data_konfirmasi = $data_konfirmasi->sortByDesc('created_at')->values();

        return view('pimpinan.konfirmasi', compact('data_konfirmasi'));
    }

    public function tanggapan(Request $request, $id)
    {
        // Validasi sederhana
        $request->validate([
            'status_pimpinan' => 'required',
            'catatan_pimpinan' => 'nullable'
        ]);

        // Kirim update ke Spreadsheet
        $update = $this->updateSheet('kunjungan', $id, [
            'status_pimpinan' => $request->status_pimpinan ?? 'Sudah Direspon',
            'catatan_pimpinan' => $request->catatan_pimpinan ?? '-'
        ]);

        if (isset($update['status']) && $update['status'] === 'success') {
            return redirect()->back()->with('success', 'Konfirmasi berhasil dikirim ke Spreadsheet.');
        }

        return redirect()->back()->with('error', 'Gagal memperbarui data di Spreadsheet.');
    }
}