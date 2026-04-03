<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MasterUser extends Authenticatable {
    use HasFactory, Notifiable;

    protected $table = 'master_user';

    // Karena Anda menggunakan guarded, tidak perlu ada $fillable. prodi_id otomatis bisa diisi.
    protected $guarded = ['id'];

    // Sembunyikan password agar tidak ikut terbawa saat query data user
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role() {
        return $this->belongsTo(MasterRole::class, 'role_id');
    }

    public function kunjungan() {
        return $this->hasMany(Kunjungan::class, 'user_id');
    }

    // TINGGAL TAMBAHKAN BAGIAN INI SAJA:
    public function prodi() {
        return $this->belongsTo(MasterProdiInstansi::class, 'prodi_id');
    }
}
