<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBatch extends Model
{
    use HasFactory;

    // Constants untuk status
    const STATUS_PROSES = 1;
    const STATUS_KIRIM = 2;
    const STATUS_RANKING = 3;
    const STATUS_VERIFIKASI = 4;
    const STATUS_REVISI = 5;
    const STATUS_UTAMA = 6;
    const STATUS_VERIFIKASI_UNIVERSITAS = 7;
    const STATUS_FINISH = 8;

    // Kolom lain dari tabel yang dapat diisi
    protected $fillable = [
        'periode_id',
        'unit_id',
        'batch',
        'status',
        'finish',
    ];
}
