<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectHasilUsaha;
use App\Supports\ApiWika;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ProjectHasilUsahaSyncService
{
    private const MAX_PERIOD_RETRIES = 10;

    private const LSP_FIELDS = [
        'lsp_review',
        'lsp_ra',
        'lsp_ri',
        'lsp_proyeksi',
    ];

    public function resolveProfitCenter(Project $project): ?string
    {
        return $project->profit_center
            ?? data_get($project->meta, 'profit_center');
    }

    public function isLspEmpty(?ProjectHasilUsaha $record): bool
    {
        if (!$record) {
            return true;
        }

        foreach (self::LSP_FIELDS as $field) {
            $value = $record->{$field};
            if ($value !== null && $value !== '' && (float) $value != 0.0) {
                return false;
            }
        }

        return true;
    }

    public function needsSync(?ProjectHasilUsaha $record): bool
    {
        return !$record || $this->isLspEmpty($record);
    }

    public function getLatestForProject(Project $project, bool $autoSync = true): ?ProjectHasilUsaha
    {
        $record = ProjectHasilUsaha::where('project_id', $project->id)
            ->orderByDesc('period')
            ->first();

        if ($autoSync && $this->needsSync($record)) {
            return $this->syncProject($project) ?? $record;
        }

        return $record;
    }

    public function syncProject(Project $project, ?string $period = null, bool $force = false): ?ProjectHasilUsaha
    {
        $profitCenter = $this->resolveProfitCenter($project);
        if (!$profitCenter) {
            return null;
        }

        $startPeriod = $period ?? now()->format('Ym');
        $dateCheck = Carbon::createFromFormat('Ym', $startPeriod)->startOfMonth();
        $latestRecord = null;

        for ($i = 0; $i < self::MAX_PERIOD_RETRIES; $i++) {
            $currentPeriod = $dateCheck->format('Ym');

            $existing = ProjectHasilUsaha::where('project_id', $project->id)
                ->where('period', $currentPeriod)
                ->first();

            if (!$force && $existing && !$this->needsSync($existing)) {
                return $existing;
            }

            $record = $this->fetchAndStore($project, $profitCenter, $currentPeriod);
            if ($record) {
                $latestRecord = $record;

                if (!$this->isLspEmpty($record)) {
                    return $record;
                }
            }

            $dateCheck->subMonth();
        }

        return $latestRecord;
    }

    public function syncAll(?string $period = null, bool $force = false): array
    {
        @set_time_limit(600);

        $period = $period ?? now()->format('Ym');
        $summary = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
            'refreshed_empty' => 0,
            'failed_rows' => [],
        ];

        $projects = Project::query()
            ->where(function ($query) {
                $query->whereNotNull('profit_center')
                    ->orWhereNotNull('meta->profit_center');
            })
            ->get();

        foreach ($projects as $project) {
            $profitCenter = $this->resolveProfitCenter($project);
            if (!$profitCenter) {
                $summary['skipped']++;
                continue;
            }

            $existing = ProjectHasilUsaha::where('project_id', $project->id)
                ->where('period', $period)
                ->first();

            $shouldForce = $force || $this->needsSync($existing);

            try {
                $record = $this->syncProject($project, $period, $shouldForce);

                if ($record) {
                    $summary['success']++;
                    if ($existing && $this->needsSync($existing) && !$this->needsSync($record)) {
                        $summary['refreshed_empty']++;
                    }
                } else {
                    $summary['failed']++;
                    $summary['failed_rows'][] = "Proyek '{$project->project_name}': data hasil usaha tidak ditemukan dari API.";
                }
            } catch (\Throwable $e) {
                $summary['failed']++;
                $summary['failed_rows'][] = "Proyek '{$project->project_name}': " . $e->getMessage();
                Log::channel('sync_wika_log')->error('[Sync Hasil Usaha] Gagal sinkron proyek.', [
                    'project_id' => $project->id,
                    'period' => $period,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $summary;
    }

    private function fetchAndStore(Project $project, string $profitCenter, string $period): ?ProjectHasilUsaha
    {
        $apiResponse = (new ApiWika())->getHasilUsahaProject($period, $profitCenter);

        if (!$apiResponse || empty($apiResponse['status']) || !isset($apiResponse['data']['hasil_usaha'])) {
            return null;
        }

        $apiData = $apiResponse['data']['hasil_usaha'];

        return ProjectHasilUsaha::updateOrCreate(
            [
                'project_id' => $project->id,
                'period' => $period,
            ],
            [
                'profit_center' => $profitCenter,
                'response_data' => $apiResponse['data'],
                'kontrak_review_total' => $apiData['kontrak_review_total'] ?? 0,
                'kontrak_review' => $apiData['kontrak_review'] ?? 0,
                'progress_fisik_ra' => $apiData['progress_fisik_ra'] ?? 0,
                'progress_fisik_ri' => $apiData['progress_fisik_ri'] ?? 0,
                'penjualan_ra' => $apiData['penjualan_ra'] ?? 0,
                'penjualan_ri' => $apiData['penjualan_ri'] ?? 0,
                'lsp_review' => $apiData['lsp_review'] ?? 0,
                'lsp_ra' => $apiData['lsp_ra'] ?? 0,
                'lsp_ri' => $apiData['lsp_ri'] ?? 0,
                'lsp_proyeksi' => $apiData['lsp_proyeksi'] ?? 0,
            ]
        );
    }
}
