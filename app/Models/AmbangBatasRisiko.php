<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AmbangBatasRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ambang_batas_risikos';

    protected $fillable = [
        'periode_id',
        'nilai_kapasitas_risiko',
        'nilai_selera_risiko',
        'nilai_toleransi_risiko',
        'nilai_batasan_risiko'
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
