<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SekolahSetting extends Model
{
    protected $table = 'sekolah_settings';

    protected $fillable = [
        'nama_sekolah',
        'alamat',
        'latitude',
        'longitude',
        'radius_meter',
        'toleransi_terlambat_menit',
    ];

    public static function getSetting(): self
    {
        return self::firstOrCreate([], [
            'nama_sekolah' => 'SMK Negeri 1',
            'alamat' => 'Jl. Pendidikan No. 1',
            'latitude' => -6.20000000,
            'longitude' => 106.81666600,
            'radius_meter' => 100,
            'toleransi_terlambat_menit' => 15,
        ]);
    }
}
