<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekomendasiKRI extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekomendasi_kris';

    protected $fillable = [
        'rekomendasi_risiko_id',
        'kri_id',
        'kri',
        'satuan_kri',
        'batas_aman',
        'batas_waspada',
        'batas_bahaya',
    ];

    public function rekomendasiRisiko()
    {
        return $this->belongsTo(RekomendasiRisiko::class, 'rekomendasi_risiko_id');
    }
}
