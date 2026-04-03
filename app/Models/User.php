<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // Tambahkan ini

#[Fillable(['name', 'email', 'password', 'role_id', 'prodi_id', 'foto'])] // Pastikan prodi_id ada di sini
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'master_user';

    /**
     * Relasi ke tabel master_prodi_instansi
     * Memungkinkan pemanggilan $user->prodi->nama di View
     */
    public function prodi(): BelongsTo
{
    // Hubungkan ke MasterProdiInstansi, bukan Prodi
    return $this->belongsTo(MasterProdiInstansi::class, 'prodi_id');
}

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
