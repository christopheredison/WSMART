<?php

namespace App\Traits;

use Carbon\CarbonInterface;

trait FormatsRiskStatusForExport
{
    /**
     * Status Open/Closed untuk export.
     * Jika tahun & bulan laporan diisi, penutupan dinilai pada bulan laporan itu
     * sehingga hasilnya sama persis dengan badge status di halaman monitoring.
     * Contoh: ditutup pada monitoring Agustus → laporan Juli Open, Agustus Closed.
     */
    public function formatStatusRisikoForExport(?int $year = null, ?int $month = null): string
    {
        if ($year && $month) {
            $isClosed = $this->isClosedAsOf($year, $month);
        } else {
            $isClosed = (bool) $this->is_closed;
        }

        if (!$isClosed) {
            return 'Open';
        }

        $closedAt = $this->resolveClosedAtForExport();
        if ($closedAt) {
            $namaBulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
            ];

            $bulan = $namaBulan[(int) $closedAt->format('n')] ?? $closedAt->format('F');
            $tahun = $closedAt->format('Y');

            return "Closed ({$bulan} {$tahun})";
        }

        return 'Closed';
    }

    /**
     * Ambil tanggal penutupan untuk export.
     * Prioritas: closed_at, lalu updated_at (untuk data lama yang hanya punya is_closed).
     */
    protected function resolveClosedAtForExport(): ?CarbonInterface
    {
        if ($this->closed_at instanceof CarbonInterface) {
            return $this->closed_at;
        }

        if (!empty($this->closed_at)) {
            return $this->asDateTime($this->closed_at);
        }

        if ($this->updated_at instanceof CarbonInterface) {
            return $this->updated_at;
        }

        if (!empty($this->updated_at)) {
            return $this->asDateTime($this->updated_at);
        }

        return null;
    }
}
