<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class tahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'nama',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'status' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'tahun_ajaran_id');
    }

    public function kegiatanSekolah(): HasMany
    {
        return $this->hasMany(KegiatanSekolah::class, 'tahun_ajaran_id');
    }
}
