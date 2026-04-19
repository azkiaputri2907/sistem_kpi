<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            // Menambah kolom status dan catatan dari pimpinan
            $table->string('status_pimpinan')->default('Menunggu')->after('status_layanan');
            $table->text('catatan_pimpinan')->nullable()->after('status_pimpinan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kunjungan', function (Blueprint $table) {
            // Menghapus kolom jika migration di-rollback
            $table->dropColumn(['status_pimpinan', 'catatan_pimpinan']);
        });
    }
};
