<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RisikoUniversitas extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function rencanaKegiatan()
    {
        return $this->belongsTo(RencanaKegiatan::class, 'rencana_kegiatan');
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }

    public function areaDampak()
    {
        return $this->belongsTo(AreaDampak::class, 'area_dampak');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function tck()
    {
        return $this->belongsTo(Tck::class, 'target_capaian_kinerja');
    }

    public function skalaProbabilitas()
    {
        return $this->belongsTo(SkalaProbabilitas::class, 'skala_probabilitas_id');
    }
}
