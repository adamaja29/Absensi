<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fail before creating the new table if legacy data already violates the new indexes.
        $schedules = DB::table('jadwal_pelajaran')
            ->join('jam_pelajaran as starts', 'starts.id', '=', 'jadwal_pelajaran.jam_pelajaran_mulai_id')
            ->join('jam_pelajaran as ends', 'ends.id', '=', 'jadwal_pelajaran.jam_pelajaran_selesai_id')
            ->select('jadwal_pelajaran.*', 'starts.jam_mulai as starts_at', 'ends.jam_selesai as ends_at')
            ->get();
        $occupied = [];
        foreach ($schedules as $schedule) {
            $slotIds = DB::table('jam_pelajaran')
                ->where('jenis', 'pelajaran')
                ->where('jam_mulai', '>=', $schedule->starts_at)
                ->where('jam_selesai', '<=', $schedule->ends_at)
                ->orderBy('urutan')
                ->orderBy('jam_mulai')
                ->pluck('id');
            if ($slotIds->isEmpty()) {
                $slotIds = collect([$schedule->jam_pelajaran_mulai_id]);
            }

            foreach ($slotIds as $slotId) {
                foreach (['guru_id', 'kelas_id'] as $dimension) {
                    $key = implode('|', [$dimension, $schedule->tahun_ajaran_id, $schedule->hari, $schedule->{$dimension}, $slotId]);
                    if (isset($occupied[$key])) {
                        throw new RuntimeException("Jadwal lama #{$schedule->id} bentrok pada jam_pelajaran_id {$slotId}. Selesaikan bentrok jadwal lama sebelum menjalankan migrasi.");
                    }
                    $occupied[$key] = $schedule->id;
                }
            }
        }

        Schema::create('jadwal_pelajaran_sesi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_pelajaran_id')->constrained('jadwal_pelajaran')->cascadeOnDelete();
            $table->foreignId('jam_pelajaran_id')->constrained('jam_pelajaran')->cascadeOnDelete();
            // Denormalized scope columns let the database enforce timetable conflicts atomically.
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('hari', 20);
            $table->timestamps();

            $table->unique(['jadwal_pelajaran_id', 'jam_pelajaran_id'], 'jps_meeting_slot_unique');
            $table->unique(['tahun_ajaran_id', 'hari', 'guru_id', 'jam_pelajaran_id'], 'jps_teacher_slot_unique');
            $table->unique(['tahun_ajaran_id', 'hari', 'kelas_id', 'jam_pelajaran_id'], 'jps_class_slot_unique');
        });

        // Backfill existing start/end ranges into explicit occupied sessions.
        foreach ($schedules as $schedule) {
            $slots = DB::table('jam_pelajaran')
                ->where('jenis', 'pelajaran')
                ->where('jam_mulai', '>=', $schedule->starts_at)
                ->where('jam_selesai', '<=', $schedule->ends_at)
                ->orderBy('urutan')
                ->orderBy('jam_mulai')
                ->get(['id']);

            if ($slots->isEmpty()) {
                $slots = collect([(object) ['id' => $schedule->jam_pelajaran_mulai_id]]);
            }

            $now = now();
            foreach ($slots as $slot) {
                DB::table('jadwal_pelajaran_sesi')->insert([
                    'jadwal_pelajaran_id' => $schedule->id,
                    'jam_pelajaran_id' => $slot->id,
                    'tahun_ajaran_id' => $schedule->tahun_ajaran_id,
                    'guru_id' => $schedule->guru_id,
                    'kelas_id' => $schedule->kelas_id,
                    'hari' => $schedule->hari,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran_sesi');
    }
};
