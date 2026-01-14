<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBatch extends Model
{
    use HasFactory;

    // Constants untuk status
    const STATUS_PROSES = 1; // Draft
    const STATUS_KIRIM = 2; // Masuk ke Risk Owner Project/Divis
    const STATUS_RANKING = 3;
    const STATUS_VERIFIKASI = 4; // Proses Verifikasi
    const STATUS_REVISI = 5; // Dikembalikan ke Drafter
    const STATUS_UTAMA = 6;
    const STATUS_VERIFIKASI_CORPORATE = 7;
    const STATUS_FINISH = 8;
    const STATUS_REJECTED_FROM_OFFICER_MR = 9; // Dikembalikan dari Risk Officer MR ke Divisi
    const STATUS_REJECTED_FROM_OWNER_MR = 10; // Dikembalikan dari Risk Owner MR ke Risk Officer MR

    // Kolom lain dari tabel yang dapat diisi
    protected $fillable = [
        'periode_id',
        'type',
        'unit_id',
        'project_id',
        'batch',
        'status',
        'step_verification',
        'finish',
    ];
}
