<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeristiwaRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'peristiwa_risikos';

    protected $fillable = [
        'kategori_risiko_id',
        'jenis_risiko_id',
        'title',
        'deskripsi',
        'unit_type_id',
    ];

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function rencanaPerlakuanRisiko()
    {
        return $this->hasOne(RencanaPerlakuanRisiko::class, 'risiko_id');
    }

    public function penyebabRisiko()
    {
        return $this->hasOne(PenyebabRisiko::class, 'risiko_id');
    }

    public function monitoringRisiko()
    {
        return $this->hasMany(MonitoringRisiko::class, 'risiko_id');
    }

    public function kri() {
        return $this->hasOne(KRI::class, 'risiko_id');
    }

    public function kontrolEksistings()
    {
        return $this->hasMany(KontrolEksisting::class, 'peristiwa_risiko_id');
    }
}
