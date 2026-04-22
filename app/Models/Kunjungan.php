<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model {
    protected $table = 'kunjungan';

    // Ini yang mengizinkan status dan waktu masuk ke database
    protected $guarded = ['id'];

    // TAMBAHKAN INI: Agar waktu_selesai otomatis jadi objek format waktu (Carbon)
            protected $casts = [
            'waktu_selesai_layanan' => 'datetime',
            'tanggal' => 'date',
            'skor_pelayanan' => 'float', // Tambahkan ini agar angka desimal diproses dengan benar
        ];

    // Pencarian data di URL otomatis menggunakan kolom nomor_kunjungan
    public function getRouteKeyName()
    {
        return 'nomor_kunjungan';
    }

    public function pengunjung() {
        return $this->belongsTo(Pengunjung::class, 'pengunjung_id');
    }

    public function prodi() {
        return $this->belongsTo(MasterProdiInstansi::class, 'prodi_id');
    }

    public function keperluan_master() {
        return $this->belongsTo(MasterKeperluan::class, 'keperluan_id');
    }

    public function admin() {
        return $this->belongsTo(MasterUser::class, 'user_id');
    }

    public function survey() {
        return $this->hasOne(Survey::class, 'kunjungan_id');
    }
}
