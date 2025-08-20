<?php

namespace App\Exports;

use App\Models\KamusRisikoUnit;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KamusRisikoUnitExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $filters;

    public function __construct(array $filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = KamusRisikoUnit::query()->with([
            'identifikasiRisiko.unit',
            'identifikasiRisiko.jenisRisiko.kategoriRisiko',
            'identifikasiRisiko.riskAnalysis'
        ]);

        // Terapkan filter dari request
        if (!empty($this->filters['unit_id'])) {
            $query->whereHas('identifikasiRisiko', fn($q) => $q->where('unit_id', $this->filters['unit_id']));
        }
        if (!empty($this->filters['peristiwa_risiko'])) {
            $query->whereHas('identifikasiRisiko', fn($q) => $q->where('peristiwa_risiko', 'like', '%' . $this->filters['peristiwa_risiko'] . '%'));
        }
        if (!empty($this->filters['jenis_risiko_id'])) {
            $query->whereHas('identifikasiRisiko', fn($q) => $q->where('jenis_risiko_id', $this->filters['jenis_risiko_id']));
        }
        if (!empty($this->filters['level_risiko'])) {
            $query->whereHas('identifikasiRisiko', fn($q) => $q->where('level_risiko', $this->filters['level_risiko']));
        }
        if (!empty($this->filters['deskripsi_risiko'])) {
            $query->whereHas('identifikasiRisiko', fn($q) => $q->where('deskripsi_peristiwa_risiko', 'like', '%' . $this->filters['deskripsi_risiko'] . '%'));
        }
        if (!empty($this->filters['efektivitas'])) {
            $query->whereHas('identifikasiRisiko', function ($q) {
                if ($this->filters['efektivitas'] == 'efektif') {
                    $q->where('efektivitas_perlakuan_risiko', '>', 0);
                } elseif ($this->filters['efektivitas'] == 'tidak_efektif') {
                    $q->where('efektivitas_perlakuan_risiko', '<=', 0);
                }
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Divisi',
            'Taksonomi Risiko',
            'Peristiwa Risiko',
            'Deskripsi Peristiwa Risiko',
            'Nilai Dampak Inheren',
            'Skala Dampak Inheren',
            'Nilai Probabilitas Inheren',
            'Eksposur Risiko Inheren',
            'Level Risiko Inheren',
            'Skala Risiko Inheren',
            'Realisasi Nilai Dampak',
            'Realisasi Skala Dampak',
            'Realisasi Skala Probabilitas',
            'Realisasi Level Risiko',
            'Realisasi Eksposur Risiko',
            'Efektivitas',
        ];
    }

    public function map($row): array
    {
        $risiko = $row->identifikasiRisiko;
        $analisa = $risiko?->riskAnalysis;
        
        $kategori = $risiko?->jenisRisiko?->kategoriRisiko?->title ?? 'N/A';
        $jenis = $risiko?->jenisRisiko?->title ?? 'N/A';

        return [
            $risiko?->unit?->name ?? '-',
            $kategori . ' - ' . $jenis,
            $risiko?->peristiwa_risiko ?? '-',
            $risiko?->deskripsi_peristiwa_risiko ?? '-',
            $analisa?->nilai_dampak ?? 0,
            $analisa?->skala_dampak ?? '-',
            $analisa?->nilai_probabilitas ?? 0,
            $analisa?->eksposur_risiko ?? 0,
            $analisa?->level_risiko ?? '-',
            $analisa?->skala_risiko ?? '-',
            $analisa?->nilai_dampak_residual ?? 0,
            $analisa?->skala_dampak_residual ?? '-',
            $analisa?->skalaProbabilitasResidual?->tingkat ?? '-',
            $analisa?->level_risiko_residual ?? '-',
            $analisa?->eksposur_risiko_residual ?? 0,
            $risiko?->efektivitas_perlakuan_risiko ?? '-',
        ];
    }
}