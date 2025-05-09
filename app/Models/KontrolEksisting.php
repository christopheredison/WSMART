<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KontrolEksisting extends Model
{
    use HasFactory;

    protected $fillable = [
        'peristiwa_risiko_id',//berelasi dengan Model Peristiwa Risiko
        'kontrol_eksisting',
    ];

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }
}
