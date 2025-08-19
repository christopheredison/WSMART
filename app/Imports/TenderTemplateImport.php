<?php

namespace App\Imports;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TenderTemplateImport
{
    protected $templatePath;
    protected $peristiwaRisikoData;
    protected $jenisRisikoData;

    public function __construct($templatePath, $peristiwaRisikoData, $jenisRisikoData)
    {
        $this->templatePath = $templatePath;
        $this->peristiwaRisikoData = $peristiwaRisikoData;
        $this->jenisRisikoData = $jenisRisikoData;
    }

    public function getModifiedSpreadsheet(): Spreadsheet
    {
        // Muat file Excel yang ada
        $spreadsheet = IOFactory::load($this->templatePath);

        $this->addStandarisasiRisikoSheet($spreadsheet);
        $this->addTaksonomiSheet($spreadsheet);

        return $spreadsheet;
    }
    protected function addStandarisasiRisikoSheet(Spreadsheet $spreadsheet): void
    {
        $worksheet = new Worksheet($spreadsheet, 'Standarisasi Risiko');
        $spreadsheet->addSheet($worksheet, 0);
        $worksheet->getTabColor()->setARGB('FFFF0000');

        $worksheet->setCellValue('A1', 'ID');
        $worksheet->setCellValue('B1', 'Standarisasi Risiko');
        $this->applyHeaderStyle($worksheet, 'A1:B1');
        $worksheet->getColumnDimension('A')->setWidth(10);
        $worksheet->getColumnDimension('B')->setWidth(50);

        $row = 2;
        foreach ($this->peristiwaRisikoData as $item) {
            $worksheet->setCellValue('A' . $row, $item->id);
            $worksheet->setCellValue('B' . $row, $item->title);
            $row++;
        }

        if ($row > 2) {
            $this->applyBorderStyle($worksheet, 'A1:B' . ($row - 1));
        }
    }

    protected function addTaksonomiSheet(Spreadsheet $spreadsheet): void
    {
        $worksheet = new Worksheet($spreadsheet, 'Taksonomi');
        $spreadsheet->addSheet($worksheet, 1);
        $worksheet->getTabColor()->setARGB('FFFF0000');

        $worksheet->setCellValue('A1', 'ID');
        $worksheet->setCellValue('B1', 'Taksonomi');
        $this->applyHeaderStyle($worksheet, 'A1:B1');
        $worksheet->getColumnDimension('A')->setWidth(10);
        $worksheet->getColumnDimension('B')->setWidth(70);

        $row = 2;
        foreach ($this->jenisRisikoData as $item) {
            $kategoriTitle = $item->kategoriRisiko ? $item->kategoriRisiko->title : 'N/A';
            $taksonomiText = "{$kategoriTitle} - {$item->title}";

            $worksheet->setCellValue('A' . $row, $item->id);
            $worksheet->setCellValue('B' . $row, $taksonomiText);
            $row++;
        }

        if ($row > 2) {
            $this->applyBorderStyle($worksheet, 'A1:B' . ($row - 1));
        }
    }

    private function applyHeaderStyle(Worksheet $worksheet, string $range): void
    {
        $style = [
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFE0E0E0'],
            ],
        ];
        $worksheet->getStyle($range)->applyFromArray($style);
    }

    private function applyBorderStyle(Worksheet $worksheet, string $range): void
    {
        $style = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $worksheet->getStyle($range)->applyFromArray($style);
    }
}