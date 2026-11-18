<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalPelajaran extends Model
{
    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'tahun_ajaran_id',
        'guru_id',
        'kelas_id',
        'mata_pelajaran_id',
        'hari',
        'jam_pelajaran_mulai_id',
        'jam_pelajaran_selesai_id',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(tahunAjaran::class, 'tahun_ajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(guru::class, 'guru_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataPelajaran(): BelongsTo
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function jamMulai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_mulai_id');
    }

    public function jamSelesai(): BelongsTo
    {
        return $this->belongsTo(JamPelajaran::class, 'jam_pelajaran_selesai_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(AbsensiGuru::class, 'jadwal_pelajaran_id');
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(JadwalPelajaranSesi::class, 'jadwal_pelajaran_id');
    }
}
