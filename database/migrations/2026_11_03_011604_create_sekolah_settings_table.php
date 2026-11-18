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
        Schema::create('sekolah_settings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('Sekolah Menengah Kejuruan');
            $table->text('alamat')->nullable();
            $table->decimal('latitude', 10, 8)->default(-6.20000000);
            $table->decimal('longitude', 11, 8)->default(106.81666600);
            $table->integer('radius_meter')->default(100);
            $table->integer('toleransi_terlambat_menit')->default(15);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah_settings');
    }
};
