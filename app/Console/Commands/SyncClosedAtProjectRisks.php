<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Audit;
use App\Models\ProjectRisk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SyncClosedAtProjectRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project-risks:sync-closed-at';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set closed_at pada project_risks berdasarkan bulan/tahun monitoring terakhir untuk risiko yang is_closed = true. Data yang closed_at-nya sudah sesuai bulan/tahun monitoring di-skip. Log ditulis ke file baru per tanggal.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses sinkronisasi closed_at untuk Project Risks...');

        $logFileName = 'sync_project_risks_closed_at_' . now()->format('Y-m-d') . '.log';
        $logPath = storage_path('logs/' . $logFileName);

        $logger = Log::build([
            'driver' => 'single',
            'path' => $logPath,
        ]);

        $logger->info('=== START SYNC PROJECT RISKS: ' . now() . ' ===');
        $logger->info('Log file: ' . $logFileName);

        $totalData = ProjectRisk::where(function ($query) {
            $query->where('is_closed', true)
                  ->orWhere('is_closed', 1);
        })->count();

        if ($totalData === 0) {
            $this->info('Tidak ada data risiko proyek dengan status is_closed = true.');
            $logger->info('=== END SYNC: Tidak ada data yang diproses ===');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $updatedCount = 0;
        $alreadyCorrectCount = 0;
        $noMonitoringCount = 0;
        $updatedIds = [];
        $alreadyCorrectIds = [];
        $noMonitoringIds = [];

        ProjectRisk::where(function ($query) {
                $query->where('is_closed', true)
                      ->orWhere('is_closed', 1);
            })
            ->chunkById(100, function ($risks) use (
                $bar,
                $logger,
                &$updatedCount,
                &$alreadyCorrectCount,
                &$noMonitoringCount,
                &$updatedIds,
                &$alreadyCorrectIds,
                &$noMonitoringIds
            ) {
                foreach ($risks as $risk) {
                    $latestMonitoring = $risk->projectRiskMonitorings()
                        ->orderBy('tahun', 'desc')
                        ->orderBy('month', 'desc')
                        ->orderBy('id', 'desc')
                        ->first();

                    if (!$latestMonitoring) {
                        $logger->warning("[TANPA MONITORING - SKIP] Project Risk ID: {$risk->id}", [
                            'status' => 'skip_no_monitoring',
                            'risk_id' => $risk->id,
                            'peristiwa_risiko' => $risk->deskripsi_peristiwa_risiko ?? $risk->rencana_kegiatan,
                            'closed_at_sekarang' => optional($risk->closed_at)?->toDateTimeString(),
                            'reason' => 'Tidak memiliki data monitoring pelaporan sama sekali.',
                        ]);
                        $noMonitoringCount++;
                        $noMonitoringIds[] = $risk->id;
                        $bar->advance();
                        continue;
                    }

                    $monitoringYear = (int) ($latestMonitoring->tahun ?: optional($latestMonitoring->updated_at)?->year ?: now()->year);
                    $monitoringMonth = max(1, min(12, (int) $latestMonitoring->month));
                    $expectedClosedAt = Carbon::create($monitoringYear, $monitoringMonth, 1)->endOfMonth();

                    $context = [
                        'risk_id' => $risk->id,
                        'peristiwa_risiko' => $risk->deskripsi_peristiwa_risiko ?? $risk->rencana_kegiatan,
                        'closed_at_sekarang' => optional($risk->closed_at)?->toDateTimeString(),
                        'last_monitoring' => [
                            'monitoring_id' => $latestMonitoring->id,
                            'quarter' => $latestMonitoring->quarter,
                            'month' => $monitoringMonth,
                            'tahun' => $monitoringYear,
                            'updated_at' => optional($latestMonitoring->updated_at)?->toDateTimeString(),
                        ],
                    ];

                    $alreadyCorrect = $risk->closed_at
                        && (int) $risk->closed_at->format('Y') === $monitoringYear
                        && (int) $risk->closed_at->format('n') === $monitoringMonth;

                    if ($alreadyCorrect) {
                        $logger->info("[SUDAH BENAR - SKIP] Project Risk ID: {$risk->id}", array_merge($context, [
                            'status' => 'skip_already_correct',
                            'reason' => 'closed_at sudah sesuai bulan dan tahun monitoring terakhir.',
                            'closed_at_diharapkan' => $expectedClosedAt->toDateTimeString(),
                        ]));
                        $alreadyCorrectCount++;
                        $alreadyCorrectIds[] = $risk->id;
                        $bar->advance();
                        continue;
                    }

                    $logger->info("[PERLU UPDATE] Project Risk ID: {$risk->id}", array_merge($context, [
                        'status' => 'need_update',
                        'closed_at_baru' => $expectedClosedAt->toDateTimeString(),
                        'reason' => $risk->closed_at
                            ? 'closed_at tidak sesuai bulan/tahun monitoring terakhir.'
                            : 'closed_at masih kosong.',
                    ]));

                    $oldClosedAt = optional($risk->closed_at)?->toDateTimeString();

                    $risk->closed_at = $expectedClosedAt;
                    $risk->saveQuietly();

                    $this->writeClosedAtAudit(
                        $risk,
                        $oldClosedAt,
                        $expectedClosedAt->toDateTimeString(),
                        $latestMonitoring->id,
                        $monitoringMonth,
                        $monitoringYear
                    );

                    $updatedCount++;
                    $updatedIds[] = $risk->id;
                    $bar->advance();
                }
            });

        $bar->finish();

        $logger->info('=== RINGKASAN ===', [
            'perlu_diupdate' => $updatedCount,
            'sudah_benar_skip' => $alreadyCorrectCount,
            'tanpa_monitoring_skip' => $noMonitoringCount,
            'ids_perlu_diupdate' => $updatedIds,
            'ids_sudah_benar' => $alreadyCorrectIds,
            'ids_tanpa_monitoring' => $noMonitoringIds,
        ]);
        $logger->info("=== END SYNC: update {$updatedCount}, skip sudah benar {$alreadyCorrectCount}, skip tanpa monitoring {$noMonitoringCount} ===");

        $this->newLine(2);
        $this->info("Proses selesai!");
        $this->line("Perlu diupdate : <info>{$updatedCount}</info>");
        $this->line("Sudah benar (skip) : <comment>{$alreadyCorrectCount}</comment>");
        if ($noMonitoringCount > 0) {
            $this->warn("Tanpa monitoring (skip) : {$noMonitoringCount}");
        }
        $this->line("Detail log: <comment>storage/logs/{$logFileName}</comment>");

        return Command::SUCCESS;
    }

    private function writeClosedAtAudit(
        ProjectRisk $risk,
        ?string $oldClosedAt,
        string $newClosedAt,
        int $monitoringId,
        int $monitoringMonth,
        int $monitoringYear
    ): void {
        Audit::query()->create([
            'user_type' => null,
            'user_id' => null,
            'event' => 'updated',
            'auditable_type' => ProjectRisk::class,
            'auditable_id' => $risk->id,
            'old_values' => [
                'closed_at' => $oldClosedAt,
            ],
            'new_values' => [
                'closed_at' => $newClosedAt,
                'monitoring_id' => $monitoringId,
                'monitoring_month' => $monitoringMonth,
                'monitoring_tahun' => $monitoringYear,
                'sync_command' => 'project-risks:sync-closed-at',
            ],
            'url' => 'artisan project-risks:sync-closed-at',
            'ip_address' => null,
            'user_agent' => 'console',
            'tags' => 'sync-closed-at',
            'project_risk_id' => $risk->id,
            'unit_id' => $risk->unit_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
