<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JamPelajaran extends Model
{
    protected $table = 'jam_pelajaran';

    protected $fillable = [
        'nama_sesi',
        'jenis',
        'jam_mulai',
        'jam_selesai',
        'urutan',
    ];

    public function getFormatJamAttribute(): string
    {
        $mulai = substr($this->jam_mulai, 0, 5);
        $selesai = substr($this->jam_selesai, 0, 5);
        return "{$this->nama_sesi} ({$mulai} - {$selesai})";
    }

    public function getJenisLabelAttribute(): string
    {
        return match ($this->jenis) {
            'upacara' => 'Upacara / Apel',
            'istirahat' => 'Istirahat',
            'kegiatan_lain' => 'Kegiatan Lain',
            default => 'Jam Pelajaran (KBM)',
        };
    }
}
