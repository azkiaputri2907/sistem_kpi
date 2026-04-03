<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('master_user', function (Blueprint $table) {
            // Menambahkan kolom prodi_id tepat setelah role_id agar rapi
            $table->unsignedBigInteger('prodi_id')->nullable()->after('role_id');
        });
    }

    public function down(): void
    {
        Schema::table('master_user', function (Blueprint $table) {
            $table->dropColumn('prodi_id');
        });
    }
};
