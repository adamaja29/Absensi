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
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();

            $table->string('nama', 9);

            $table->enum('semester', ['ganjil', 'genap']);

            $table->date('tanggal_mulai');

            $table->date('tanggal_selesai');

            $table->boolean('status')->default(false);

            $table->timestamps();

            $table->unique(['nama', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tahun_ajaran');
    }
};
