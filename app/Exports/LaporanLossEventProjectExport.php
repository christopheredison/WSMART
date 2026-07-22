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
use PhpOffice\PhpSpreadsheet\Style\Font;

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

        // 7. Format Tanggal Kejadian
        $tanggalKejadian = $row->tanggal_kejadian ? \Carbon\Carbon::parse($row->tanggal_kejadian)->format('d/m/Y') : '-';

        // 8. Mapping Teridentifikasi & Link Risk Register
        $teridentifikasi = $row->project_risk_id ? 'Yes' : 'No';
        $linkRiskRegister = '-';

        if ($row->project_risk_id && $row->risiko) {
            // Generate URL ke detail risiko
            $url = route('projects.risks.view', [
                'project' => $row->risiko->project_periode_list_id,
                'risk'    => $row->project_risk_id
            ]);

            // Ambil deskripsi dan bersihkan karakter kutip ganda untuk format formula excel
            $namaHyperlink = $row->risiko->deskripsi_peristiwa_risiko ?? 'Lihat Detail';
            $namaHyperlink = str_replace('"', '""', $namaHyperlink);
            
            // Format output hyperlink untuk excel
            $linkRiskRegister = '=HYPERLINK("' . $url . '", "' . $namaHyperlink . '")';
        }

        return [
            $this->rowNumber,
            $tanggalKejadian,
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
            $teridentifikasi,
            $linkRiskRegister, // Kolom U
            optional(optional($row->risiko)->projectRiskAnalisa)->nilai_dampak ?? 0,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Kejadian',
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
            'Link Risk Register', // Kolom U
            'Biaya Risiko Inheren'
        ];
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'N' => $currencyFormat,
            'R' => $currencyFormat,
            'S' => $currencyFormat,
            'V' => $currencyFormat,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Heading Bold
        $sheet->getStyle('1')->getFont()->setBold(true);

        // Agar teks yang ada \n (newline) bisa tampil rapi (wrap text)
        $sheet->getStyle('H:I')->getAlignment()->setWrapText(true);

        // Menghitung total baris data (termasuk heading baris 1)
        $totalRows = $this->rowNumber + 1;

        if ($totalRows > 1) {
            // Target baris data dari baris 2 sampai baris terakhir pada Kolom U (Link Risk Register)
            $linkRange = 'U2:U' . $totalRows;

            // Menerapkan warna biru standard (#0563C1) dan underline (garis bawah)
            $sheet->getStyle($linkRange)->getFont()
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0563C1'))
                ->setUnderline(Font::UNDERLINE_SINGLE);
        }

        return [];
    }
}
