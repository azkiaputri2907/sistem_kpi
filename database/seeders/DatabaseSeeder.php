<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterProdiInstansi;
use App\Models\MasterKeperluan;
use App\Models\MasterAspekSurvey;
use App\Models\MasterPertanyaan;
use App\Models\MasterRole;
use App\Models\MasterUser;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $password = Hash::make('password123');

        // --- 1. MENGISI DATA MASTER ROLE ---
        // Wajib dijalankan pertama agar User bisa merujuk ke ID ini
        $roles = [
            ['id' => 1, 'nama_role' => 'Super Admin'],
            ['id' => 2, 'nama_role' => 'Admin Prodi'],
            ['id' => 3, 'nama_role' => 'Ketua Jurusan'],
            ['id' => 4, 'nama_role' => 'Ketua Prodi'],
        ];

        foreach ($roles as $role) {
            MasterRole::updateOrCreate(['id' => $role['id']], $role);
        }

        // --- 2. MENGISI DATA MASTER PRODI ---
        $prodis = [
            ['nama' => 'Teknik Informatika', 'jenis' => 'Prodi'],
            ['nama' => 'Elektronika', 'jenis' => 'Prodi'],
            ['nama' => 'Sistem Informasi Kota Cerdas', 'jenis' => 'Prodi'],
            ['nama' => 'Teknik Listrik', 'jenis' => 'Prodi'],
            ['nama' => 'Teknologi Rekayasa Pembangkit Energi', 'jenis' => 'Prodi'],
            ['nama' => 'Teknologi Rekayasa Otomasi', 'jenis' => 'Prodi'],
            ['nama' => 'Pimpinan Jurusan', 'jenis' => 'Prodi'],

        ];

        foreach ($prodis as $p) {
            MasterProdiInstansi::updateOrCreate(['nama' => $p['nama']], $p);
        }

        // --- 3. MENGISI DATA MASTER KEPERLUAN ---
        $keperluans = [
            ['keterangan' => 'Legalisir Ijazah'],
            ['keterangan' => 'Konsultasi Tugas Akhir'],
            ['keterangan' => 'Penyerahan Berkas / Laporan'],
            ['keterangan' => 'Lainnya'],
        ];

        foreach ($keperluans as $k) {
            MasterKeperluan::updateOrCreate(['keterangan' => $k['keterangan']], $k);
        }

        // --- 4. MENGISI DATA MASTER USER (FULL SEMUA PRODI) ---

        // A. Akun Global (Role 1 & 3)
        MasterUser::updateOrCreate(
            ['email' => 'super@poliban.ac.id'],
            ['role_id' => 1, 'name' => 'Super Administrator', 'password' => $password]
        );

        MasterUser::updateOrCreate(
            ['email' => 'kajur.elektro@poliban.ac.id'],
            ['role_id' => 3, 'name' => 'Ketua Jurusan Elektro', 'password' => $password]
        );

        // B. Akun Per Prodi (Admin & Kaprodi)
        $list_prodi_user = [
            'ti'      => 'Teknik Informatika',
            'elka'    => 'Elektronika',
            'listrik' => 'Teknik Listrik',
            'trpe'    => 'Teknologi Rekayasa Pembangkit Energi',
            'sikc'    => 'Sistem Informasi Kota Cerdas',
            'tro'     => 'Teknologi Rekayasa Otomasi',
        ];

        foreach ($list_prodi_user as $slug => $nama) {
            // Admin Prodi (Role 2)
            MasterUser::updateOrCreate(
                ['email' => "admin.$slug@poliban.ac.id"],
                ['role_id' => 2, 'name' => "Admin $nama", 'password' => $password]
            );

            // Kaprodi (Role 4)
            MasterUser::updateOrCreate(
                ['email' => "kaprodi.$slug@poliban.ac.id"],
                ['role_id' => 4, 'name' => "Kaprodi $nama", 'password' => $password]
            );
        }

        // --- 5. MENGISI DATA MASTER ASPEK & PERTANYAAN SURVEY ---
        $aspeks = [
            'Kecepatan Pelayanan' => 'Bagaimana kecepatan admin dalam memberikan pelayanan?',
            'Sikap Admin'         => 'Bagaimana penerapan budaya 5S (Senyum, Sapa, Salam, Sopan, Santun) admin saat melayani?',
            'Kualitas Informasi'  => 'Apakah admin memberikan informasi atau solusi yang jelas?',
            'Sarana & Prasarana'  => 'Bagaimana kenyamanan dan kebersihan fasilitas pelayanan?',
            'Kepuasan Umum'       => 'Seberapa puas kamu dengan pelayanan admin secara keseluruhan?',
        ];

        foreach ($aspeks as $namaAspek => $isiPertanyaan) {
            $aspek = MasterAspekSurvey::updateOrCreate(['nama_aspek' => $namaAspek]);

            MasterPertanyaan::updateOrCreate(
                ['aspek_id' => $aspek->id],
                ['pertanyaan' => $isiPertanyaan]
            );
        }
    }
}
