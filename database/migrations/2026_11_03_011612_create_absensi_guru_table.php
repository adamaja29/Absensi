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
        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jadwal_pelajaran_id')
                ->constrained('jadwal_pelajaran')
                ->cascadeOnDelete();

            $table->foreignId('guru_id')
                ->constrained('guru')
                ->cascadeOnDelete();

            $table->date('tanggal');
            $table->time('waktu_absen');
            $table->enum('status', ['hadir', 'terlambat', 'izin', 'sakit'])->default('hadir');

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('jarak_meter')->nullable();

            $table->text('materi_diajarkan')->nullable();
            $table->text('catatan_siswa_tidak_masuk')->nullable();
            $table->text('keterangan')->nullable();

            $table->timestamps();

            $table->unique(['jadwal_pelajaran_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_guru');
    }
};
