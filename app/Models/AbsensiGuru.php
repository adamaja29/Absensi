<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbsensiGuru extends Model
{
    protected $table = 'absensi_guru';

    protected $fillable = [
        'jadwal_pelajaran_id',
        'guru_id',
        'tanggal',
        'waktu_absen',
        'status',
        'latitude',
        'longitude',
        'jarak_meter',
        'materi_diajarkan',
        'catatan_siswa_tidak_masuk',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function jadwalPelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(guru::class, 'guru_id');
    }
}
