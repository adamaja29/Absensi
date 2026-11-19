<?php

namespace App\Support;

use App\Models\JamPelajaran;
use Illuminate\Support\Collection;

class AbsenceBlockResolver
{
    /**
     * Map same-subject schedule rows to one meeting, including sessions split
     * only by one or more scheduled breaks.
     */
    public static function resolve(Collection $schedules): array
    {
        $schedules->loadMissing(['jamMulai', 'jamSelesai']);

        $groups = $schedules->groupBy(fn ($schedule) => implode('|', [
            $schedule->tahun_ajaran_id,
            $schedule->guru_id,
            $schedule->kelas_id,
            $schedule->mata_pelajaran_id,
            $schedule->hari,
        ]));

        $slots = JamPelajaran::orderBy('urutan')->orderBy('jam_mulai')->get();
        $blocks = [];
        foreach ($groups as $group) {
            $ranges = $group->map(fn ($schedule) => [
                'id' => $schedule->id,
                'start' => $schedule->jamMulai->jam_mulai,
                'end' => $schedule->jamSelesai->jam_selesai,
                'start_order' => $schedule->jamMulai->urutan,
                'end_order' => $schedule->jamSelesai->urutan,
            ])->sortBy('start_order')->values();

            $current = [];
            $currentEnd = null;
            $currentEndOrder = null;
            foreach ($ranges as $range) {
                $interveningSlots = $current
                    ? $slots->filter(fn ($slot) => $slot->urutan > $currentEndOrder && $slot->urutan < $range['start_order'])->values()
                    : collect();
                $isSeparatedOnlyByBreaks = $interveningSlots->isNotEmpty()
                    && $interveningSlots->every(fn ($slot) => $slot->jenis === 'istirahat')
                    && $interveningSlots->first()->jam_mulai === $currentEnd
                    && $interveningSlots->last()->jam_selesai === $range['start']
                    && $interveningSlots->sliding(2)->every(
                        fn ($pair) => $pair->first()->jam_selesai === $pair->last()->jam_mulai
                    );
                $isContinuous = $current && (
                    $range['start'] === $currentEnd
                    || $isSeparatedOnlyByBreaks
                );

                if ($current && !$isContinuous) {
                    foreach ($current as $id) {
                        $blocks[$id] = $current;
                    }
                    $current = [];
                    $currentEnd = null;
                    $currentEndOrder = null;
                }

                $current[] = $range['id'];
                $currentEnd = $range['end'];
                $currentEndOrder = $range['end_order'];
            }

            foreach ($current as $id) {
                $blocks[$id] = $current;
            }
        }

        return $blocks;
    }

    public static function attachAttendance(Collection $schedules, Collection $attendance): Collection
    {
        $blocks = self::resolve($schedules);
        $attendanceBySchedule = $attendance->keyBy('jadwal_pelajaran_id');

        foreach ($schedules as $schedule) {
            $record = null;
            foreach ($blocks[$schedule->id] ?? [$schedule->id] as $scheduleId) {
                if ($attendanceBySchedule->has($scheduleId)) {
                    $record = $attendanceBySchedule->get($scheduleId);
                    break;
                }
            }

            $schedule->setRelation('absensi', $record ? collect([$record]) : collect());
        }

        return $schedules;
    }
}
