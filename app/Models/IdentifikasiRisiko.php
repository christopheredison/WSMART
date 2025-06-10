<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdentifikasiRisiko extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_type_id',
        'unit_id',
        'periode_id',
        'user_id',
        'kategori_risiko_id',
        'jenis_risiko_id',
        'peristiwa_risiko_id',
        'target_capaian_kinerja',
        'rencana_kegiatan',
        'peristiwa_risiko',
        'deskripsi_peristiwa_risiko',
        'deskripsi_rencana_kegiatan',
        'type',
        'jenis_kontrol_eksisting_id',
        'kontrol_eksisting',
        'penilaian_efektivitas_kontrol',
        'perkiraan_waktu_terpapar_risiko_mulai',
        'perkiraan_waktu_terpapar_risiko_akhir',
        'status_risiko',
        'status_progress',
        'type_risiko',
        'status_progress',
        'type_risiko',
        'catatan',
        'skala_risiko',
        'level_risiko',
        'status',
        'is_corporate',
    ];

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
        return $this->belongsTo(Tck::class, 'tck_id');
    }

    public function kris() {
        return $this->hasMany(KRI::class, 'risiko_id');
    }

    public function jenisKontrolEksisting()
    {
        return $this->belongsTo(JenisKontrolEksisting::class, 'jenis_kontrol_eksisting_id');
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

    public const STATUS_INPUT_DATA = 1;
    public const STATUS_DIKIRIM = 2;
    public const STATUS_TUNGGU_VERIFIKASI = 3;
    public const STATUS_TERVERIFIKASI = 4;

    public const LEVEL_RISIKO_LOW = 'Low';
    public const LEVEL_RISIKO_LOW_TO_MODERATE = 'Low To Moderate';
    public const LEVEL_RISIKO_MODERATE = 'Moderate';
    public const LEVEL_RISIKO_MODERATE_TO_HIGH = 'Moderate To High';
    public const LEVEL_RISIKO_HIGH = 'High';
}
