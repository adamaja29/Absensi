<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IzinGuru extends Model
{
    protected $table = 'izin_guru';

    protected $fillable = [
        'guru_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'bukti_file',
        'status',
        'disetujui_oleh',
        'catatan_waka',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(guru::class, 'guru_id');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }
}
