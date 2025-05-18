<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MetrikStrategiRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'metrik_strategi_risikos';

    protected $fillable = [
        'periode_id',
        'kategori_risiko_id',
        'jenis_risiko_id',
        'risk_appetite_statement',
        'sikap_risiko_id',
        'peristiwa_risiko_id'
    ];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class);
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class);
    }

    public function sikapRisiko()
    {
        return $this->belongsTo(SikapRisiko::class);
    }

    public function parameterMetriks()
    {
        return $this->hasMany(ParameterMetrik::class);
    }

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class);
    }
}
