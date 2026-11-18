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
        Schema::create('kegiatan_sekolah', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tahun_ajaran_id')
                ->constrained('tahun_ajaran')
                ->cascadeOnDelete();

            $table->string('nama_kegiatan');
            $table->enum('jenis', ['upacara', 'istirahat', 'ibadah', 'senam', 'literasi', 'lainnya'])->default('upacara');
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi')->nullable(); // Lapangan Utama, Masjid, Kantin, dll
            $table->string('peserta')->nullable(); // Seluruh Siswa & Guru, dll
            $table->text('keterangan')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_sekolah');
    }
};
