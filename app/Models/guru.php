<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'no_hp',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jadwalPelajaran(): HasMany
    {
        return $this->hasMany(JadwalPelajaran::class, 'guru_id');
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(AbsensiGuru::class, 'guru_id');
    }

    public function izin(): HasMany
    {
        return $this->hasMany(IzinGuru::class, 'guru_id');
    }
}
