<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Kunjungan;

class NotifikasiPimpinanMail extends Mailable
{
    use Queueable, SerializesModels;

    public $kunjungan;

    public function __construct(Kunjungan $kunjungan)
    {
        $this->kunjungan = $kunjungan;
    }

    public function build()
    {
        return $this->subject('Notifikasi Antrean: ' . $this->kunjungan->nomor_kunjungan)
                    ->view('emails.notifikasi_pimpinan');
    }
}
