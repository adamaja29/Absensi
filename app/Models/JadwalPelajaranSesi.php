<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalPelajaranSesi extends Model
{
    protected $table = 'jadwal_pelajaran_sesi';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'jam_pelajaran_id',
        'tahun_ajaran_id',
        'guru_id',
        'kelas_id',
        'hari',
    ];

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function jamPelajaran(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_id');
    }
}
