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
        Schema::table('jam_pelajaran', function (Blueprint $table) {
            $table->enum('jenis', ['pelajaran', 'upacara', 'istirahat', 'kegiatan_lain'])
                ->default('pelajaran')
                ->after('nama_sesi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jam_pelajaran', function (Blueprint $table) {
            $table->dropColumn('jenis');
        });
    }
};
