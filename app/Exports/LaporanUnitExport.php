<?php

namespace App\Exports;

use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\Unit\ProfilRisikoSheet;
use App\Exports\Sheets\Unit\RisikoInherentKuantitatifSheet;
use App\Exports\Sheets\Unit\RisikoInherentKualitatifSheet;
use App\Exports\Sheets\Unit\RisikoResidualKuantitatifSheet;
use App\Exports\Sheets\Unit\RisikoResidualKualitatifSheet;
use App\Exports\Sheets\Unit\RencanaPerlakuanRisikoSheet;
use App\Exports\Sheets\Unit\RealisasiResidualSheet;

class LaporanUnitExport implements WithMultipleSheets
{
    protected $periodeId;
    protected $unitId;
    protected $bulan;
    protected $tahun;

    public function __construct(int $periodeId, int $unitId, $bulan = null)
    {
        $this->periodeId = $periodeId;
        $this->unitId = $unitId;
        $this->bulan = $bulan;

        $periode = Periode::find($periodeId);
        $this->tahun = $periode ? $periode->tahun : null;
    }

    public function sheets(): array
    {
        $semuaRisiko = IdentifikasiRisiko::with([
            'unit',
            'periode',
            'kategoriRisiko',
            'jenisRisiko',
            'peristiwaRisiko',
            
            // Menggunakan relasi hasMany agar bisa diurutkan untuk mendapat data fallback terbaru
            'penyebabRisiko.perlakuanPenyebabRisikoUnit.perlakuanPenyebabUnitMonitorings' => function($q) {
                $q->whereHas('unitRiskMonitoring', function($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan); // Fallback ke bulan sebelumnya
                    }
                });
                $q->orderBy('id', 'desc');
            },
            
            'dampakRisikos.perlakuanDampakRisikos.perlakuanDampakMonitorings' => function($q) {
                $q->whereHas('unitRiskMonitoring', function($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan); // Fallback ke bulan sebelumnya
                    }
                });
                $q->orderBy('id', 'desc');
            },
            
            'kris.kriUnitMonitorings' => function($q) {
                $q->whereHas('unitRiskMonitoring', function($sq) {
                    $sq->where('status', 100)->where('is_approved', 1);
                    if ($this->bulan) {
                        $sq->where('month', '<=', $this->bulan); // Fallback ke bulan sebelumnya
                    }
                });
                $q->orderBy('id', 'desc');
            },
            
            'kontrolEksistings',
            'jenisKontrolEksisting',
            'penilaianEfektifitasKontrol',
            'riskAnalysis.skalaDampakObj',
            'riskAnalysis.skalaProbabilitas',
            'riskAnalysis.areaDampakObj',
            'riskAnalysis.skalaDampakResidualQ1Obj',
            'riskAnalysis.skalaProbabilitasResidualQ1',
            'riskAnalysis.skalaDampakResidualQ2Obj',
            'riskAnalysis.skalaProbabilitasResidualQ2',
            'riskAnalysis.skalaDampakResidualQ3Obj',
            'riskAnalysis.skalaProbabilitasResidualQ3',
            'riskAnalysis.skalaDampakResidualQ4Obj',
            'riskAnalysis.skalaProbabilitasResidualQ4',
            
            // Relasi Monitoring Utama
            'monitoringRisikos' => function($query) {
                // Pastikan data yang diambil adalah data yang sudah di-approve
                $query->where('status', 100)->where('is_approved', 1);
                
                if ($this->bulan) {
                    // Gunakan operator <= agar jika bulan yang dipilih kosong, 
                    // otomatis menarik data bulan sebelumnya
                    $query->where('month', '<=', $this->bulan);
                }
                
                // Urutkan dari bulan terbesar (terdekat dengan yang dipilih) 
                // lalu ID terbaru jika ada multi-data di bulan yang sama
                $query->orderBy('month', 'desc')->orderBy('id', 'desc');
            },
            
            'monitoringRisikos.skalaProbabilitas',
            'monitoringRisikos.skalaDampakObj'
        ])
        ->where('periode_id', $this->periodeId)
        ->where('unit_id', $this->unitId)
        ->get()
        ->sortByDesc('riskAnalysis.skala_risiko');

        $sheets = [
            new ProfilRisikoSheet($semuaRisiko, $this->bulan),
            new RisikoInherentKuantitatifSheet($semuaRisiko),
            new RisikoInherentKualitatifSheet($semuaRisiko),
            new RisikoResidualKuantitatifSheet($semuaRisiko),
            new RisikoResidualKualitatifSheet($semuaRisiko),
            new RencanaPerlakuanRisikoSheet($semuaRisiko),
            new RealisasiResidualSheet($semuaRisiko, $this->bulan),
        ];

        return $sheets;
    }
}
