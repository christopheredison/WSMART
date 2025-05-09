<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentifikasiRisiko extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'identifikasi_risikos';

    public function penyebabRisiko()
    {
        return $this->hasMany(PenyebabRisiko::class, 'risiko_id');
    }

    public function riskAnalysis()
    {
        return $this->hasOne(RiskAnalysis::class, 'risiko_id');
    }

    public function rencanaPerlakuanRisiko()
    {
        return $this->hasOne(RencanaPerlakuanRisiko::class, 'risiko_id');
    }

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

    public function kris() {
        return $this->hasMany(KRI::class, 'risiko_id');
    }

    public function toDraftStructure() {
        $basic = $this->toArray();
        $basic['penyebab_risiko_ids'] = $this->penyebabRisiko->pluck('id')->toArray();
        $basic['penyebab_risiko'] = $this->penyebabRisiko->pluck('penyebab_risiko')->toArray();
        $basic['key_risk_indicator_ids'] = [];
        $basic['key_risk_indicator'] = [];
        $basic['satuan_kri'] = [];
        $basic['batas_aman'] = [];
        $basic['batas_waspada'] = [];
        $basic['batas_bahaya'] = [];

        foreach ($this->kris as $kri) {
            $basic['key_risk_indicator_ids'][] = $kri->id;
            $basic['key_risk_indicator'][] = $kri->kri;
            $basic['satuan_kri'][] = $kri->satuan_kri;
            $basic['batas_aman'][] = $kri->batas_aman;
            $basic['batas_waspada'][] = $kri->batas_waspada;
            $basic['batas_bahaya'][] = $kri->batas_bahaya;
        }

        $basic['perkiraan_waktu_terpapar_risiko'] = implode(' ', [
            date('d/m/Y', strtotime($this->perkiraan_waktu_terpapar_risiko_mulai)),
            'to',
            date('d/m/Y', strtotime($this->perkiraan_waktu_terpapar_risiko_akhir))
        ]);

        return $basic;
    }
}
