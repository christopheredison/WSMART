<?php

namespace App\Exports;

use App\Models\LossEventProject;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanLossEventProjectExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected $projectIds;
    protected $rowNumber = 0;

    public function __construct(array $projectIds)
    {
        $this->projectIds = $projectIds;
    }

    public function collection()
    {
        return LossEventProject::with([
            'project',
            'peristiwaRisiko',
            'kategoriKejadian',
            'penyebabRisikoProjectLeds.perlakuanPenyebabRisiko',
            'kategoriRisiko',
            'jenisRisiko',
            'risiko.projectRiskAnalisa'
        ])
        ->whereIn('project_id', $this->projectIds)
        ->get();
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $sumberPenyebab = match ($row->sumber_penyebab_kejadian) {
            '1' => 'Internal',
            '2' => 'Eksternal',
            default => $row->sumber_penyebab_kejadian,
        };

        $penyebabArr = $row->penyebabRisikoProjectLeds->pluck('penyebab_risiko')->toArray();
        $penyebabString = implode("\n- ", $penyebabArr);
        if(count($penyebabArr) > 0) $penyebabString = "- " . $penyebabString;

        $penangananArr = $row->penyebabRisikoProjectLeds->map(function($item) {
            return optional($item->perlakuanPenyebabRisiko)->rencana_perlakuan_risiko ?? '-';
        })->toArray();
        $penangananString = implode("\n- ", $penangananArr);
        if(count($penangananArr) > 0) $penangananString = "- " . $penangananString;

        $kategoriBumn = match ($row->kategori_risiko_bumn) {
            '1' => 'Financial',
            '2' => 'Operational',
            '3' => 'Public & Legal',
            default => '-',
        };

        $kategoriT2T3 = (optional($row->kategoriRisiko)->title ?? '-') . ' - ' . (optional($row->jenisRisiko)->title ?? '-');

        return [
            $this->rowNumber,
            optional($row->project)->project_name ?? '-',
            $row->nama_kejadian,
            optional($row->peristiwaRisiko)->title,
            optional($row->kategoriKejadian)->kategori_kejadian,
            $sumberPenyebab,
            $penyebabString,
            $penangananString,
            $row->deskripsi_kejadian,
            $kategoriBumn,
            $kategoriT2T3,
            $row->penjelasan_kerugian,
            $row->nilai_kerugian_finansial,
            $row->kejadian_berulang,
            $row->frekuensi_kejadian,
            $row->rencana_mitigasi,
            $row->realisasi_mitigasi,
            $row->perbaikan_mendatang,
            $row->unit_penanggung_jawab,
            $row->status_asuransi,
            $row->nilai_premi,
            $row->nilai_klaim,
            $row->project_risk_id ? 'Yes (ID: '.$row->project_risk_id.')' : 'No',
            $row->no_urut_risiko,
            optional(optional($row->risiko)->projectRiskAnalisa)->nilai_dampak ?? 0,
            $row->biaya_upaya_perbaikan,
            $row->hasil_perbaikan,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Proyek', // Tambahan Header
            'Nama Kejadian',
            'Identifikasi Kejadian',
            'Kategori Kejadian',
            'Sumber Penyebab',
            'Penyebab Kejadian',
            'Penanganan Saat Kejadian',
            'Deskripsi Kejadian',
            'Kategori Risiko BUMN',
            'Kategori Risiko T2 & T3 KBUMN',
            'Penjelasan Kerugian',
            'Nilai Kerugian',
            'Kejadian Berulang',
            'Frekuensi Kejadian',
            'Mitigasi yang Direncanakan',
            'Realisasi Mitigasi',
            'Perbaikan Mendatang',
            'Pihak Terkait',
            'Status Asuransi',
            'Nilai Premi',
            'Nilai Klaim',
            'Teridentifikasi di Risk Register',
            'No Urut Risiko',
            'Biaya Risiko Inheren',
            'Biaya Upaya Perbaikan',
            'Hasil dari Perbaikan'
        ];
    }

    public function columnFormats(): array
    {
        // Format Currency digeser huruf kolomnya karena ada tambahan 1 kolom di depan
        // L -> M (Nilai Kerugian)
        // T -> U (Nilai Premi)
        // U -> V (Nilai Klaim)
        // X -> Y (Biaya Risiko Inheren)
        // Y -> Z (Biaya Upaya Perbaikan)
        // Z -> AA (Hasil Perbaikan)

        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'M' => $currencyFormat, // Sebelumnya L
            'U' => $currencyFormat, // Sebelumnya T
            'V' => $currencyFormat, // Sebelumnya U
            'Y' => $currencyFormat, // Sebelumnya X
            'Z' => $currencyFormat, // Sebelumnya Y
            'AA' => $currencyFormat, // Sebelumnya Z
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [ 1 => ['font' => ['bold' => true]] ];
    }
}
