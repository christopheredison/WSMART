<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrolEksisting extends Model
{
    use HasFactory;

    protected $fillable = [
        'risiko_id',
        'peristiwa_risiko_id',//berelasi dengan Model Peristiwa Risiko
        'kontrol_eksisting',
    ];

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }


    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }
}
