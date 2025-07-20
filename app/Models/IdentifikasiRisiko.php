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
        'step_verification',
        'is_corporate',
        'previous_status_risiko',
    ];

    protected $guarded = [];
    protected $table = 'identifikasi_risikos';

    public function penyebabRisiko()
    {
        return $this->hasMany(PenyebabRisiko::class, 'risiko_id');
    }

    public function penyebabRisikos()
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

    public function penilaianEfektifitasKontrol()
    {
        return $this->belongsTo(PenilaianEfektivitasKontrol::class, 'penilaian_efektifitas_kontrol');
    }

    public function monitoringRisiko()
    {
        return $this->hasOne(UnitRiskMonitoring::class, 'identifikasi_risiko_id');
    }

    public function monitoringRisikos()
    {
        return $this->hasMany(UnitRiskMonitoring::class, 'identifikasi_risiko_id');
    }

    public function lastMonitoringRisiko()
    {
        return $this->hasOne(UnitRiskMonitoring::class, 'identifikasi_risiko_id')->orderBy('id', 'desc');
    }

    public function kontrolEksistings()
    {
        return $this->hasMany(KontrolEksisting::class, 'risiko_id');
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

    public function getCurrentRiskMapsAttribute() {
        $currentRiskMaps = [
            'inherent' => [
                'skala_dampak' => $this->riskAnalysis?->skala_dampak,
                'skala_probabilitas' => $this->riskAnalysis?->skalaProbabilitas?->tingkat,
                'quarter' => 0,
            ],
        ];

        $projectMonitoringQ1 = $this->monitoringRisikos->where('quarter', 1)->first();
        $projectMonitoringQ2 = $this->monitoringRisikos->where('quarter', 2)->first();
        $projectMonitoringQ3 = $this->monitoringRisikos->where('quarter', 3)->first();
        $projectMonitoringQ4 = $this->monitoringRisikos->where('quarter', 4)->first();
        $currentRiskMaps[1] = [
            'skala_dampak' => $projectMonitoringQ1?->skala_dampak,
            'skala_probabilitas' => $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
            'quarter' => 1,
        ];
        $currentRiskMaps[2] = [
            'skala_dampak' => $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
            'skala_probabilitas' => $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
            'quarter' => 2,
        ];
        $currentRiskMaps[3] = [
            'skala_dampak' => $projectMonitoringQ3?->skala_dampak ?? $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
            'skala_probabilitas' => $projectMonitoringQ3?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
            'quarter' => 3,
        ];
        $currentRiskMaps[4] = [
            'skala_dampak' => $projectMonitoringQ4?->skala_dampak ?? $projectMonitoringQ3?->skala_dampak ?? $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
            'skala_probabilitas' => $projectMonitoringQ4?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ3?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
            'quarter' => 4,
        ];

        return $currentRiskMaps;
    }

    public function getCurrentRiskMapsMonthAttribute() {
        $currentRiskMaps = [
            'inherent' => [
                'skala_dampak' => $this->riskAnalysis?->skala_dampak,
                'skala_probabilitas' => $this->riskAnalysis?->skalaProbabilitas?->tingkat,
                'quarter' => 0,
            ],
        ];

        $currentRiskMap = $currentRiskMaps['inherent'];

        for ($month = 1; $month <= 12; $month++) {
            $projectMonitoring = $this->monitoringRisikos->where('month', $month)->first();
            $currentRiskMaps[$month] = [
                'skala_dampak' => $projectMonitoring?->skala_dampak ?? $currentRiskMap['skala_dampak'],
                'skala_probabilitas' => $projectMonitoring?->skalaProbabilitas?->tingkat ?? $currentRiskMap['skala_probabilitas'],
                'month' => $month,
            ];

            $currentRiskMap = $currentRiskMaps[$month];
        }

        return $currentRiskMaps;
    }

    public const STATUS_INPUT_DATA = 1;
    public const STATUS_DIKIRIM = 2;
    public const STATUS_TUNGGU_VERIFIKASI = 3;
    public const STATUS_TERVERIFIKASI = 4;
    public const STATUS_REJECTED = 5;

    public const LEVEL_RISIKO_LOW = 'Low';
    public const LEVEL_RISIKO_LOW_TO_MODERATE = 'Low To Moderate';
    public const LEVEL_RISIKO_MODERATE = 'Moderate';
    public const LEVEL_RISIKO_MODERATE_TO_HIGH = 'Moderate To High';
    public const LEVEL_RISIKO_HIGH = 'High';

    public const STATUS_RISIKO_REGULAR = 1;
    public const STATUS_RISIKO_RECOMMENDATION = 2;
    public const STATUS_RISIKO_MAIN = 3;
    public const STATUS_RISIKO_CORPORATE_RECOMMENDATION = 4;
    public const STATUS_RISIKO_CORPORATE = 5;

    public function refreshRealisasi()
    {
        $this->load('penyebabRisiko.perlakuanPenyebabRisikoUnit.perlakuanPenyebabUnitMonitorings.unitRiskMonitoring', 'kris.kriUnitMonitorings.unitRiskMonitoring');
    
        $perlakuanPenyebabRisikos = $this->penyebabRisiko->flatten()->pluck('perlakuanPenyebabRisikoUnit')->flatten();
        $kris = $this->kris->flatten();
    
        $perlakuanPenyebabRisikos->each(function($perlakuanPenyebabRisiko) {
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $realisasiBiaya = $perlakuanPenyebabRisiko
                    ->perlakuanPenyebabUnitMonitorings
                    ->filter(fn($monitoring) => $monitoring->unitRiskMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->realisasi_biaya_perlakuan_risiko;
    
                $progress = $perlakuanPenyebabRisiko
                    ->perlakuanPenyebabUnitMonitorings
                    ->filter(fn($monitoring) => $monitoring->unitRiskMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->progress_rencana_perlakuan_risiko;
    
                $perlakuanPenyebabRisiko->update([
                    "realisasi_biaya_perlakuan_risiko_q{$quarter}" => $realisasiBiaya,
                    "progress_rencana_perlakuan_risiko_q{$quarter}" => $progress,
                ]);
            }
        });
    
        $kris->each(function($kri) {
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $nilaiKri = $kri
                    ->kriUnitMonitorings
                    ->filter(fn($monitoring) => $monitoring->unitRiskMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->nilai_kri_terkini;
    
                $statusKri = $kri
                    ->kriUnitMonitorings
                    ->filter(fn($monitoring) => $monitoring->unitRiskMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->status_kri_terkini;
    
                $kri->update([
                    "nilai_kri_terkini_q{$quarter}" => $nilaiKri,
                    "status_kri_terkini_q{$quarter}" => $statusKri,
                ]);
            }
        });
    }

    public const PROGRESS_ON_REVIEW = 1;
    public const PROGRESS_ON_REVISION_DELETED = 2;
    public const PROGRESS_ON_ACCEPTED = 3;
    public const PROGRESS_ON_FINAL = 4;
}
