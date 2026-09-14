<?php

namespace App\Exports;

use App\Models\LossEvent;
use App\Models\LossEventAp;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;

class LaporanLossEventUnitExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithColumnFormatting
{
    protected array $unitIds;
    protected ?int $periodeId;
    protected string $scope;
    protected string $unitNameHeading;
    protected string $riskViewRoute;
    protected int $rowNumber = 0;

    public function __construct(array $unitIds, string $scope = 'unit', ?int $periodeId = null)
    {
        $this->unitIds = $unitIds;
        $this->scope = $scope;
        $this->periodeId = $periodeId;
        $this->unitNameHeading = $scope === 'ap' ? 'Nama Anak Perusahaan' : 'Nama Divisi';
        $this->riskViewRoute = $scope === 'ap' ? 'risk-register-ap.view' : 'risk-register-unit.view';
    }

    public function collection()
    {
        $query = $this->scope === 'ap' ? LossEventAp::query() : LossEvent::query();

        return $query->with([
            'unit',
            'kategoriKejadian',
            'penyebabRisikoLeds.perlakuanPenyebabRisiko',
            'kategoriRisiko',
            'jenisRisiko',
            'risiko.riskAnalysis',
        ])
            ->whereIn('unit_id', $this->unitIds)
            ->when($this->periodeId, fn ($q) => $q->where('periode_id', $this->periodeId))
            ->orderBy('unit_id')
            ->orderBy('id')
            ->get();
    }

    public function map($row): array
    {
        $this->rowNumber++;

        $sumberPenyebab = match ((string) $row->sumber_penyebab_kejadian) {
            '1' => 'Internal',
            '2' => 'Eksternal',
            default => '-',
        };

        $identifikasiKejadian = $row->identifikasi_kejadian ?? '-';

        $penyebabList = [];
        $penangananList = [];

        foreach ($row->penyebabRisikoLeds as $index => $penyebab) {
            $num = $index + 1;
            $penyebabList[] = "{$num}. " . $penyebab->penyebab_risiko;

            if ($penyebab->perlakuanPenyebabRisiko->isNotEmpty()) {
                foreach ($penyebab->perlakuanPenyebabRisiko as $p) {
                    $penangananList[] = '- (Penyebab ' . $num . ') ' . ($p->rencana_perlakuan_risiko ?? '-');
                }
            }
        }

        $penyebabString = implode("\n", $penyebabList);
        $penangananString = implode("\n", $penangananList);

        $kejadianBerulang = $row->kejadian_berulang == 1 ? 'Ya' : 'Tidak';
        $frekuensi = '-';
        if ($row->kejadian_berulang == 1) {
            $frekuensi = $row->frekuensi_kejadian == 6
                ? '6 kali atau lebih per tahun'
                : ($row->frekuensi_kejadian ? $row->frekuensi_kejadian . ' kali per tahun' : '-');
        }

        $statusAsuransi = $row->status_asuransi == 1 ? 'Ya' : 'Tidak';

        $kategoriBumn = match ((string) $row->kategori_risiko_bumn) {
            '1' => 'Financial',
            '2' => 'Operational',
            '3' => 'Public & Legal',
            default => '-',
        };

        $costCenter = optional($row->unit)->cost_center ?? '-';

        $tanggalKejadian = $row->tanggal_kejadian
            ? Carbon::parse($row->tanggal_kejadian)->format('d/m/Y')
            : '-';

        $tanggalPelaporan = $row->updated_at ?? $row->created_at;
        $bulanPelaporan = $tanggalPelaporan
            ? Carbon::parse($tanggalPelaporan)->locale('id')->translatedFormat('F Y')
            : '-';

        $teridentifikasi = $row->risiko_id ? 'Yes' : 'No';
        $linkRiskRegister = '-';

        if ($row->risiko_id && $row->risiko) {
            $url = route($this->riskViewRoute, $row->risiko_id);
            $namaHyperlink = $row->risiko->deskripsi_peristiwa_risiko
                ?? $row->risiko->peristiwa_risiko
                ?? 'Lihat Detail';
            $namaHyperlink = str_replace('"', '""', $namaHyperlink);
            $linkRiskRegister = '=HYPERLINK("' . $url . '", "' . $namaHyperlink . '")';
        }

        $deskripsiKejadian = $row->deskripsi_kejadian
            ?? $row->penjelasan_kerugian
            ?? '-';

        $nilaiKerugian = $this->toNumeric($row->nilai_kerugian_finansial);
        $nilaiPremi = $this->toNumeric($row->nilai_premi);
        $nilaiKlaim = $this->toNumeric($row->nilai_klaim);
        $biayaInheren = $this->toNumeric(optional(optional($row->risiko)->riskAnalysis)->nilai_dampak ?? $row->biaya_risiko_inheren ?? 0);

        return [
            $this->rowNumber,
            $costCenter,
            optional($row->unit)->name ?? '-',
            $bulanPelaporan,
            $tanggalKejadian,
            $row->nama_kejadian ?? '-',
            $identifikasiKejadian,
            optional($row->kategoriKejadian)->kategori_kejadian ?? '-',
            $sumberPenyebab,
            $penyebabString ?: '-',
            $penangananString ?: '-',
            $deskripsiKejadian,
            $kategoriBumn,
            (optional($row->kategoriRisiko)->title ?? '-') . ' - ' . (optional($row->jenisRisiko)->title ?? '-'),
            $row->penjelasan_kerugian ?? '-',
            $nilaiKerugian,
            $kejadianBerulang,
            $frekuensi,
            $statusAsuransi,
            $nilaiPremi,
            $nilaiKlaim,
            $teridentifikasi,
            $linkRiskRegister,
            $biayaInheren,
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Cost Center',
            $this->unitNameHeading,
            'Bulan Pelaporan',
            'Tanggal Kejadian',
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
            'Link Risk Register',
            'Biaya Risiko Inheren',
        ];
    }

    public function columnFormats(): array
    {
        $currencyFormat = '_("Rp"* #,##0.00_);_("Rp"* \(#,##0.00\);_("Rp"* "-"??_);_(@_)';

        return [
            'P' => $currencyFormat,
            'T' => $currencyFormat,
            'U' => $currencyFormat,
            'X' => $currencyFormat,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        $sheet->getStyle('J:K')->getAlignment()->setWrapText(true);

        $totalRows = $this->rowNumber + 1;

        if ($totalRows > 1) {
            $linkRange = 'W2:W' . $totalRows;
            $sheet->getStyle($linkRange)->getFont()
                ->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0563C1'))
                ->setUnderline(Font::UNDERLINE_SINGLE);
        }

        return [];
    }

    private function toNumeric($value): float
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_string($value)) {
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }

        return (float) ($value ?: 0);
    }
}
