<?php

namespace App\Exports;

use App\Models\KamusRisikoProject;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KamusRisikoProjectExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = KamusRisikoProject::query()->with([
            'project',
            'projectRisk.peristiwaRisiko',
            'projectRisk.jenisRisiko.kategoriRisiko',
            'projectRisk.projectRiskAnalisa'
        ]);

        // Terapkan filter dari request
        if (!empty($this->filters['project_id'])) {
            $query->where('project_id', $this->filters['project_id']);
        }
        if (!empty($this->filters['peristiwa_risiko_id'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('peristiwa_risiko_id', $this->filters['peristiwa_risiko_id']));
        }
        if (!empty($this->filters['jenis_risiko_id'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('jenis_risiko_id', $this->filters['jenis_risiko_id']));
        }
        if (!empty($this->filters['level_risiko'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('level_risiko', $this->filters['level_risiko']));
        }
        if (!empty($this->filters['deskripsi_risiko'])) {
            $query->whereHas('projectRisk', fn($q) => $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $this->filters['deskripsi_risiko'] . '%'));
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Proyek',
            'Taksonomi Risiko',
            'Peristiwa Risiko',
            'Deskripsi Peristiwa Risiko',
            'Nilai Dampak Inheren (Rp)',
            'Skala Dampak Inheren',
            'Nilai Probabilitas Inheren (%)',
            'Eksposur Risiko Inheren (Rp)',
            'Level Risiko Inheren',
            'Realisasi Nilai Dampak (Rp)',
            'Realisasi Skala Dampak',
            'Realisasi Skala Probabilitas',
            'Realisasi Level Risiko',
            'Realisasi Eksposur Risiko (Rp)',
        ];
    }

    public function map($row): array
    {
        $kategori = $row->projectRisk?->jenisRisiko?->kategoriRisiko?->title ?? 'N/A';
        $jenis = $row->projectRisk?->jenisRisiko?->title ?? 'N/A';

        return [
            $row->project?->project_name ?? '-',
            $kategori . ' - ' . $jenis,
            $row->projectRisk?->peristiwaRisiko?->title ?? '-',
            $row->projectRisk?->deskripsi_peristiwa_risiko ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->nilai_dampak ?? 0,
            $row->projectRisk?->projectRiskAnalisa?->skala_dampak ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->nilai_probabilitas ?? 0,
            $row->projectRisk?->projectRiskAnalisa?->eksposur_risiko ?? 0,
            $row->projectRisk?->projectRiskAnalisa?->level_risiko ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->nilai_dampak_residual ?? 0,
            $row->projectRisk?->projectRiskAnalisa?->skala_dampak_residual ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->skala_probabilitas_residual ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->level_risiko_residual ?? '-',
            $row->projectRisk?->projectRiskAnalisa?->eksposur_risiko_residual ?? 0,
        ];
    }
}
