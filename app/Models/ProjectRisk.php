<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_type_id',//berelasi ke model UnitType (table unit_types)
        'unit_id', //berelasi ke model Unit (table units)
        'periode_id', //berelasi ke model Periode (table periodes)
        'user_id', //berelasi ke model User (table users)
        'project_id', //berelasi ke model Project (table projects)
        'kategori_risiko_id', //berelasi ke model KategoriRisiko (table kategori_risikos)
        'jenis_risiko_id', //berelasi ke model JenisRisiko (table jenis_risikos)
        'peristiwa_risiko_id', //berelasi ke model PeristiwaRisiko (table peristiwa_risikos)
        'project_periode_list_id', //berelasi ke model ProjectPeriodeList (table project_periode_lists)
        'target_capaian_kinerja', //berelasi ke model Tck (table tcks) -> sudah diupdate jadi text
        'rencana_kegiatan',
        'deskripsi_peristiwa_risiko',
        'type',
        'jenis_kontrol_eksisting_id',//berelasi ke model JenisKontrolEksisting (table jenis_kontrol_eksistings)
        'kontrol_eksisting', //berelasi ke model KontrolEksisting (table kontrol_eksistings)
        'penilaian_efektifitas_kontrol', //berelasi ke model PenilaianEfektivitasKontrol (table penilaian_efektivitas_kontrols)
        'perkiraan_waktu_terpapar_risiko_mulai',
        'perkiraan_waktu_terpapar_risiko_akhir',
        'status_risiko',
        'status_progress', // 1: Input Data, 2: Dikirim, 3: Tunggu Verifikasi , 4: Terverifikasi
        'type_risiko',
        'catatan',
        'skala_risiko',
        'level_risiko',
        'status',
    ];

    public const STATUS_INPUT_DATA = 1;
    public const STATUS_DIKIRIM = 2;
    public const STATUS_TUNGGU_VERIFIKASI = 3;
    public const STATUS_TERVERIFIKASI = 4;

    public const LEVEL_RISIKO_LOW = 'Low';
    public const LEVEL_RISIKO_LOW_TO_MODERATE = 'Low To Moderate';
    public const LEVEL_RISIKO_MODERATE = 'Moderate';
    public const LEVEL_RISIKO_MODERATE_TO_HIGH = 'Moderate To High';
    public const LEVEL_RISIKO_HIGH = 'High';

    public $casts = [
        'perkiraan_waktu_terpapar_risiko_mulai' => 'date:Y-m-d',
        'perkiraan_waktu_terpapar_risiko_akhir' => 'date:Y-m-d',
        'type_risiko' => 'integer',
        'skala_risiko' => 'integer',
        'status' => 'integer',
    ];

    protected $appends = [
        'perkiraan_waktu_terpapar_risiko',
    ];

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class);
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class);
    }

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class);
    }

    public function jenisKontrolEksisting()
    {
        return $this->belongsTo(JenisKontrolEksisting::class);
    }

    public function kontrolEksistingObj()
    {
        return $this->belongsTo(KontrolEksisting::class, 'kontrol_eksisting');
    }

    public function penilaianEfektivitasKontrolObj()
    {
        return $this->belongsTo(PenilaianEfektivitasKontrol::class, 'penilaian_efektifitas_kontrol');
    }

    public function projectPeriodeList()
    {
        return $this->belongsTo(ProjectPeriodeList::class);
    }

    public function projectRiskAnalisa()
    {
        return $this->hasOne(ProjectRiskAnalisa::class, 'risiko_id')->orderBy('id', 'desc');
    }

    public function projectRiskRencanaPerlakuan()
    {
        return $this->hasOne(ProjectRiskRencanaPerlakuan::class, 'risiko_id')->orderBy('id', 'desc');
    }

    public function projectRiskRencanaPerlakuans()
    {
        return $this->hasMany(ProjectRiskRencanaPerlakuan::class, 'risiko_id');
    }

    public function projectRiskAnalisas()
    {
        return $this->hasMany(ProjectRiskAnalisa::class, 'risiko_id');
    }

    public function penyebabRisikoProjects()
    {
        return $this->hasMany(PenyebabRisikoProject::class, 'risiko_id');
    }

    public function kriProjects()
    {
        return $this->hasMany(KRIProject::class, 'risiko_id');
    }

    public function projectRiskMonitoring()
    {
        return $this->hasOne(ProjectRiskMonitoring::class, 'risiko_id')->orderBy('id', 'desc');
    }

    public function projectRiskMonitorings()
    {
        return $this->hasMany(ProjectRiskMonitoring::class, 'risiko_id');
    }

    public function projectKontrolEksistings()
    {
        return $this->hasMany(ProjectKontrolEksisting::class, 'project_risk_id');
    }

    public function getPerkiraanWaktuTerpaparRisikoAttribute()
    {
        return $this->perkiraan_waktu_terpapar_risiko_mulai->format('d/m/Y') . ' to ' . $this->perkiraan_waktu_terpapar_risiko_akhir->format('d/m/Y');
    }

    public function getCurrentRiskMapsAttribute() {
        $tahuns = $this->projectRiskMonitorings()->pluck('tahun')->unique();
        $currentRiskMaps = [
            'inherent' => [
                'skala_dampak' => $this->projectRiskAnalisa?->skala_dampak,
                'skala_probabilitas' => $this->projectRiskAnalisa?->skalaProbabilitas?->tingkat,
                'quarter' => 0,
                'tahun' => 0,
            ],
        ];
        foreach ($tahuns as $tahun) {
            $projectMonitoringQ1 = $this->projectRiskMonitorings->where('quarter', 1)->where('tahun', $tahun)->first();
            $projectMonitoringQ2 = $this->projectRiskMonitorings->where('quarter', 2)->where('tahun', $tahun)->first();
            $projectMonitoringQ3 = $this->projectRiskMonitorings->where('quarter', 3)->where('tahun', $tahun)->first();
            $projectMonitoringQ4 = $this->projectRiskMonitorings->where('quarter', 4)->where('tahun', $tahun)->first();
            $currentRiskMaps[$tahun . '-' . 1] = [
                'skala_dampak' => $projectMonitoringQ1?->skala_dampak,
                'skala_probabilitas' => $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
                'quarter' => 1,
                'tahun' => $tahun,
            ];
            $currentRiskMaps[$tahun . '-' . 2] = [
                'skala_dampak' => $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
                'skala_probabilitas' => $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
                'quarter' => 2,
                'tahun' => $tahun,
            ];
            $currentRiskMaps[$tahun . '-' . 3] = [
                'skala_dampak' => $projectMonitoringQ3?->skala_dampak ?? $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
                'skala_probabilitas' => $projectMonitoringQ3?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
                'quarter' => 3,
                'tahun' => $tahun,
            ];
            $currentRiskMaps[$tahun . '-' . 4] = [
                'skala_dampak' => $projectMonitoringQ4?->skala_dampak ?? $projectMonitoringQ3?->skala_dampak ?? $projectMonitoringQ2?->skala_dampak ?? $projectMonitoringQ1?->skala_dampak,
                'skala_probabilitas' => $projectMonitoringQ4?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ3?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ2?->skalaProbabilitas?->tingkat ?? $projectMonitoringQ1?->skalaProbabilitas?->tingkat,
                'quarter' => 4,
                'tahun' => $tahun,
            ];
        }

        return $currentRiskMaps;
    }

    public function getCurrentRiskMapsMonthAttribute() {
        $tahuns = $this->projectRiskMonitorings()->pluck('tahun')->unique();
        $currentRiskMaps = [
            'inherent' => [
                'skala_dampak' => $this->projectRiskAnalisa?->skala_dampak,
                'skala_probabilitas' => $this->projectRiskAnalisa?->skalaProbabilitas?->tingkat,
                'quarter' => 0,
                'tahun' => 0,
            ],
        ];
        $currentRiskMap = $currentRiskMaps['inherent'];
        foreach ($tahuns as $tahun) {
            for ($month = 1; $month <= 12; $month++) {
                $projectMonitoring = $this->projectRiskMonitorings->where('month', $month)->where('tahun', $tahun)->first();
                $currentRiskMaps[$tahun . '-' . $month] = [
                    'skala_dampak' => $projectMonitoring?->skala_dampak ?? $currentRiskMap['skala_dampak'],
                    'skala_probabilitas' => $projectMonitoring?->skalaProbabilitas?->tingkat ?? $currentRiskMap['skala_probabilitas'],
                    'month' => $month,
                    'tahun' => $tahun,
                ];
                $currentRiskMap = $currentRiskMaps[$tahun . '-' . $month];
            }
        }

        return $currentRiskMaps;
    }

    public function refreshRealisasi()
    {
        $this->load('penyebabRisikoProjects.perlakuanPenyebabRisiko.perlakuanPenyebabMonitorings.projectMonitoring', 'kriProjects.kriProjectMonitorings.projectMonitoring');

        $perlakuanPenyebabRisikos = $this->penyebabRisikoProjects->flatten()->pluck('perlakuanPenyebabRisiko')->flatten();
        $kriProjects = $this->kriProjects->flatten();

        $perlakuanPenyebabRisikos->each(function($perlakuanPenyebabRisiko) {
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $realisasiBiaya = $perlakuanPenyebabRisiko
                    ->perlakuanPenyebabMonitorings
                    ->filter(fn($monitoring) => $monitoring->projectMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->realisasi_biaya_perlakuan_risiko;

                $progress = $perlakuanPenyebabRisiko
                    ->perlakuanPenyebabMonitorings
                    ->filter(fn($monitoring) => $monitoring->projectMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->progress_rencana_perlakuan_risiko;

                $perlakuanPenyebabRisiko->update([
                    "realisasi_biaya_perlakuan_risiko_q{$quarter}" => $realisasiBiaya,
                    "progress_rencana_perlakuan_risiko_q{$quarter}" => $progress,
                ]);
            }
        });

        $kriProjects->each(function($kriProject) {
            for ($quarter = 1; $quarter <= 4; $quarter++) {
                $nilaiKri = $kriProject
                    ->kriProjectMonitorings
                    ->filter(fn($monitoring) => $monitoring->projectMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->nilai_kri_terkini;

                $statusKri = $kriProject
                    ->kriProjectMonitorings
                    ->filter(fn($monitoring) => $monitoring->projectMonitoring->quarter === $quarter)
                    ->sortByDesc('id')
                    ->first()
                    ?->status_kri_terkini;

                $kriProject->update([
                    "nilai_kri_terkini_q{$quarter}" => $nilaiKri,
                    "status_kri_terkini_q{$quarter}" => $statusKri,
                ]);
            }
        });
    }
}
