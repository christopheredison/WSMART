<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekomendasiPenyebabRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekomendasi_penyebab_risikos';

    protected $fillable = [
        'rekomendasi_risiko_id',
        'penyebab_risiko',
    ];

    public function rekomendasiRisiko()
    {
        return $this->belongsTo(RekomendasiRisiko::class, 'rekomendasi_risiko_id');
    }
}
