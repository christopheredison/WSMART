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
        // Eager loading semua relasi agar performa cepat
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

        // 1. Mapping Sumber Penyebab
        $sumberPenyebab = match ((string)$row->sumber_penyebab_kejadian) {
            '1' => 'Internal',
            '2' => 'Eksternal',
            default => '-',
        };

        // 2. Mapping Identifikasi Kejadian
        $identifikasiKejadian = $row->peristiwa_risiko_id == 0
            ? $row->deskripsi_kejadian
            : (optional($row->peristiwaRisiko)->title ?? '-');

        // 3. Mapping Penyebab & Penanganan (Multi-line String)
        $penyebabList = [];
        $penangananList = [];

        foreach ($row->penyebabRisikoProjectLeds as $index => $penyebab) {
            $num = $index + 1;
            $penyebabList[] = "{$num}. " . $penyebab->penyebab_risiko;

            if ($penyebab->perlakuanPenyebabRisiko->isNotEmpty()) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $p) {
                    $penangananList[] = "- (Penyebab {$num}) " . ($p->rencana_perlakuan_risiko ?? '-');
                }
            }
        }
        $penyebabString = implode("\n", $penyebabList);
        $penangananString = implode("\n", $penangananList);

        // 4. Mapping Kejadian Berulang & Frekuensi
        $kejadianBerulang = $row->kejadian_berulang == 1 ? 'Ya' : 'Tidak';
        $frekuensi = '-';
        if ($row->kejadian_berulang == 1) {
            $frekuensi = $row->frekuensi_kejadian == 6
                ? '6 kali atau lebih per tahun'
                : ($row->frekuensi_kejadian ? $row->frekuensi_kejadian . ' kali per tahun' : '-');
        }

        // 5. Mapping Status Asuransi
        $statusAsuransi = $row->status_asuransi == 1 ? 'Ya' : 'Tidak';

        // 6. Kategori Risiko BUMN
        $kategoriBumn = match ((string)$row->kategori_risiko_bumn) {
            '1' => 'Financial',
            '2' => 'Operational',
            '3' => 'Public & Legal',
            default => '-',
        };

        return [
            $this->rowNumber,
            optional($row->project)->project_name ?? '-',
            $row->nama_kejadian ?? '-',
            $identifikasiKejadian,
            optional($row->kategoriKejadian)->kategori_kejadian ?? '-',
            $sumberPenyebab,
            $penyebabString ?: '-',
            $penangananString ?: '-',
            $row->deskripsi_kejadian ?? '-',
            $kategoriBumn,
            (optional($row->kategoriRisiko)->title ?? '-') . ' - ' . (optional($row->jenisRisiko)->title ?? '-'),
            $row->penjelasan_kerugian ?? '-',
            $row->nilai_kerugian_finansial ?? 0,
            $kejadianBerulang,
            $frekuensi,
            $statusAsuransi,
            $row->nilai_premi ?? 0,
            $row->nilai_klaim ?? 0,
            $row->project_risk_id ? 'Yes (ID: '.$row->project_risk_id.')' : 'No',
            optional(optional($row->risiko)->projectRiskAnalisa)->nilai_dampak ?? 0,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Proyek',
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
            'Status Asuransi',
            'Nilai Premi',
            'Nilai Klaim',
            'Teridentifikasi di Risk Register',
            'Biaya Risiko Inheren'
        ];
    }

    public function columnFormats(): array
    {
        // Penyesuaian huruf kolom karena ada penghapusan kolom di tengah:
        // M -> Nilai Kerugian
        // Q -> Nilai Premi (Sebelumnya U)
        // R -> Nilai Klaim (Sebelumnya V)
        // T -> Biaya Risiko Inheren (Sebelumnya Y)

        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'M' => $currencyFormat,
            'Q' => $currencyFormat,
            'R' => $currencyFormat,
            'T' => $currencyFormat,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Heading Bold
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Agar teks yang ada \n (newline) bisa tampil rapi (wrap text)
        $sheet->getStyle('G:H')->getAlignment()->setWrapText(true);

        return [];
    }
}
