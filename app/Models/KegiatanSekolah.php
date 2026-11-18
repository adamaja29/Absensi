<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KegiatanSekolah extends Model
{
    protected $table = 'kegiatan_sekolah';

    protected $fillable = [
        'tahun_ajaran_id',
        'nama_kegiatan',
        'jenis',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'peserta',
        'keterangan',
    ];

    public function tahunAjaran(): BelongsTo
    {
        return $this->belongsTo(tahunAjaran::class, 'tahun_ajaran_id');
    }

    public function getJenisBadgeClassAttribute(): string
    {
        return match ($this->jenis) {
            'upacara' => 'bg-primary-lt text-primary',
            'istirahat' => 'bg-warning-lt text-warning',
            'ibadah' => 'bg-teal-lt text-teal',
            'senam' => 'bg-azure-lt text-azure',
            'literasi' => 'bg-purple-lt text-purple',
            default => 'bg-secondary-lt text-secondary',
        };
    }
}
