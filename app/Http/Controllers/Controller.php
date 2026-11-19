<?php

namespace App\Http\Controllers;

use App\Models\IzinGuru;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    /** Serve a submitted izin/sakit attachment from the public disk. */
    protected function serveBuktiIzin(IzinGuru $izin): Response
    {
        $path = (string) $izin->bukti_file;

        abort_if($path === '' || !str_starts_with($path, 'bukti_izin/'), 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404, 'Lampiran tidak ditemukan.');

        return $disk->response($path, basename($path));
    }
}
