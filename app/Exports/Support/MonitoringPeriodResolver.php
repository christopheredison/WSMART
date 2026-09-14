<?php

namespace App\Exports\Support;

use App\Models\IdentifikasiRisiko;
use App\Models\Periode;
use App\Models\ProjectRisk;
use App\Models\ProjectRiskMonitoring;
use App\Models\UnitRiskMonitoring;

/**
 * Menentukan bulan monitoring yang dipotret laporan risk register.
 *
 * Laporan hanya boleh memotret bulan yang monitoringnya sudah selesai
 * (published) untuk seluruh risiko. Kalau bulan yang diminta belum selesai,
 * laporan mundur ke bulan terakhir yang sudah selesai supaya seluruh sheet
 * memakai bulan yang sama, bukan campuran bulan per risiko.
 *
 * Risiko yang sudah closed pada bulan tersebut tidak ikut dinilai karena
 * memang tidak dimonitor lagi — di laporan risiko itu tampil sebagai Closed,
 * sama seperti badge "Dihentikan" di halaman monitoring.
 *
 * Kalau tidak ada satu pun bulan yang selesai, bulan yang diminta dikembalikan
 * apa adanya sehingga laporan tetap bisa dicetak seperti sebelumnya.
 */
class MonitoringPeriodResolver
{
    /**
     * @param  array|null  $projectIds  null berarti seluruh proyek
     */
    public static function forProjects(?array $projectIds, $bulan, $tahun)
    {
        $bulan = (int) $bulan;
        $tahun = (int) $tahun;

        if ($bulan < 1 || $bulan > 12 || $tahun < 1) {
            return $bulan ?: null;
        }

        $risikos = ProjectRisk::query()
            ->whereNull('deleted_at')
            ->where('status', ProjectRisk::STATUS_PUBLISHED)
            ->when(is_array($projectIds), function ($q) use ($projectIds) {
                $q->whereIn('project_id', $projectIds);
            })
            ->get(['id', 'is_closed', 'closed_at']);

        if ($risikos->isEmpty()) {
            return $bulan;
        }

        // project_risk_monitorings.month bertipe teks, jadi perbandingan "<=" akan
        // dievaluasi secara leksikal ("2" > "10"). Pakai daftar bulan agar akurat.
        $selesaiPerBulan = ProjectRiskMonitoring::query()
            ->whereIn('risiko_id', $risikos->pluck('id'))
            ->where('tahun', $tahun)
            ->whereIn('month', range(1, $bulan))
            ->where(function ($q) {
                $q->where('status', ProjectRiskMonitoring::STATUS_PUBLISHED)
                    ->orWhere('is_approved', 1);
            })
            ->get(['risiko_id', 'month']);

        return self::bulanTerakhirSelesai($risikos, $selesaiPerBulan, 'risiko_id', $bulan, $tahun);
    }

    public static function forUnits(int $periodeId, array $unitIds, $bulan)
    {
        $bulan = (int) $bulan;

        if ($bulan < 1 || $bulan > 12 || empty($unitIds)) {
            return $bulan ?: null;
        }

        $tahun = (int) optional(Periode::find($periodeId))->tahun;
        if ($tahun < 1) {
            return $bulan;
        }

        $risikos = IdentifikasiRisiko::query()
            ->where('periode_id', $periodeId)
            ->whereIn('unit_id', $unitIds)
            ->where('status', IdentifikasiRisiko::STATUS_PUBLISHED)
            ->get(['id', 'is_closed', 'closed_at']);

        if ($risikos->isEmpty()) {
            return $bulan;
        }

        // Monitoring divisi/AP tidak punya kolom tahun; tahunnya mengikuti periode.
        $selesaiPerBulan = UnitRiskMonitoring::query()
            ->whereIn('identifikasi_risiko_id', $risikos->pluck('id'))
            ->where('month', '<=', $bulan)
            ->where('status', UnitRiskMonitoring::STATUS_PUBLISHED)
            ->where('is_approved', 1)
            ->get(['identifikasi_risiko_id', 'month']);

        return self::bulanTerakhirSelesai($risikos, $selesaiPerBulan, 'identifikasi_risiko_id', $bulan, $tahun);
    }

    /**
     * Mundur dari bulan yang diminta sampai ketemu bulan yang seluruh risikonya
     * sudah selesai dimonitor atau sudah closed pada bulan tersebut.
     */
    private static function bulanTerakhirSelesai($risikos, $monitorings, string $foreignKey, int $bulan, int $tahun): int
    {
        $selesaiPerBulan = $monitorings
            ->groupBy('month')
            ->map(function ($rows) use ($foreignKey) {
                return $rows->pluck($foreignKey)->unique()->flip();
            });

        for ($m = $bulan; $m >= 1; $m--) {
            $sudahSelesai = $selesaiPerBulan->get($m);
            if (!$sudahSelesai) {
                continue;
            }

            $lengkap = true;
            foreach ($risikos as $risiko) {
                if ($risiko->isClosedAsOf($tahun, $m)) {
                    continue;
                }
                if (!$sudahSelesai->has($risiko->id)) {
                    $lengkap = false;
                    break;
                }
            }

            if ($lengkap) {
                return $m;
            }
        }

        return $bulan;
    }
}
