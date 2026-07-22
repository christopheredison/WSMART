<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrioritasRisiko extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = [];

    public function risiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }

    public function tck()
    {
        return $this->belongsTo(Tck::class, 'target_capaian_kinerja');
    }

    public function unit() {
        return $this->belongsTo(Unit::class);
    }

    public function kategoriRisiko() {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko() {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function skalaDampak() {
        return $this->belongsTo(SkalaDampak::class, 'skala_dampak');
    }

    public function areaDampak() {
        return $this->belongsTo(AreaDampak::class, 'area_dampak');
    }

    public function rencanaKegiatan() {
        return $this->belongsTo(RencanaKegiatan::class, 'rencana_kegiatan');
    }

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function penyebabRisiko()
    {
        return $this->hasMany(PenyebabRisiko::class, 'risiko_id', 'risiko_id');
    }

    public function rencanaPerlakuanRisiko()
    {
        return $this->hasOne(RencanaPerlakuanRisiko::class, 'risiko_id', 'risiko_id');
    }
}
