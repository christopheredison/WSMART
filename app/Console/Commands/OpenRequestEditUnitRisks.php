<?php

namespace App\Console\Commands;

use App\Models\DataBatch;
use App\Models\IdentifikasiRisiko;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OpenRequestEditUnitRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unit-risks:open-request-edit
                            {--periode-id= : Filter berdasarkan periode_id}
                            {--unit-id= : Filter berdasarkan unit_id}
                            {--taksonomi=all : Filter taksonomi_risiko_id: all (semua) atau empty (hanya yang masih kosong)}
                            {--include-closed : Sertakan risiko yang sudah ditutup (is_closed)}
                            {--dry-run : Simulasi tanpa mengubah data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuka request edit risiko Divisi dan Anak Perusahaan (bukan korporat) agar status kembali ke draft. Bisa difilter taksonomi kosong atau semua.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $includeClosed = (bool) $this->option('include-closed');
        $periodeId = $this->option('periode-id');
        $unitId = $this->option('unit-id');
        $taksonomiFilter = strtolower((string) $this->option('taksonomi'));

        if (!in_array($taksonomiFilter, ['all', 'empty'], true)) {
            $this->error('Opsi --taksonomi hanya menerima nilai: all atau empty.');
            return Command::FAILURE;
        }

        $taksonomiLabel = $taksonomiFilter === 'empty'
            ? 'hanya yang taksonomi_risiko_id masih kosong'
            : 'semua (dengan atau tanpa taksonomi)';

        $this->info('Memulai pembukaan request edit risiko Divisi & Anak Perusahaan...');
        $this->line("Filter taksonomi : <comment>{$taksonomiLabel}</comment>");
        if ($dryRun) {
            $this->warn('Mode DRY-RUN: data tidak akan diubah.');
        }

        $logFileName = 'open_request_edit_unit_risks_' . now()->format('Y-m-d') . '.log';
        $logPath = storage_path('logs/' . $logFileName);
        $logger = Log::build([
            'driver' => 'single',
            'path' => $logPath,
        ]);

        $logger->info('=== START OPEN REQUEST EDIT UNIT RISKS: ' . now() . ' ===');
        $logger->info('Log file: ' . $logFileName, [
            'dry_run' => $dryRun,
            'include_closed' => $includeClosed,
            'periode_id' => $periodeId,
            'unit_id' => $unitId,
            'taksonomi' => $taksonomiFilter,
            'filter' => $taksonomiLabel,
        ]);

        $query = $this->baseQuery($includeClosed, $periodeId, $unitId, $taksonomiFilter);
        $totalData = (clone $query)->count();

        if ($totalData === 0) {
            $this->info('Tidak ada risiko Divisi/Anak Perusahaan yang perlu dibuka sesuai filter.');
            $logger->info('=== END: Tidak ada data yang diproses ===');
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($totalData);
        $bar->start();

        $updatedCount = 0;
        $skippedAlreadyOpenCount = 0;
        $failedCount = 0;
        $updatedIds = [];
        $skippedAlreadyOpenIds = [];
        $failedIds = [];

        (clone $query)->with('unit')->orderBy('id')->chunkById(100, function ($risks) use (
            $bar,
            $logger,
            $dryRun,
            &$updatedCount,
            &$skippedAlreadyOpenCount,
            &$failedCount,
            &$updatedIds,
            &$skippedAlreadyOpenIds,
            &$failedIds
        ) {
            foreach ($risks as $risk) {
                $context = [
                    'risk_id' => $risk->id,
                    'unit_id' => $risk->unit_id,
                    'unit_name' => $risk->unit->name ?? null,
                    'unit_type_id' => $risk->unit_type_id,
                    'periode_id' => $risk->periode_id,
                    'peristiwa_risiko' => $risk->peristiwa_risiko ?? $risk->deskripsi_peristiwa_risiko,
                    'status_sekarang' => $risk->status,
                    'status_progress_sekarang' => $risk->status_progress,
                    'step_verification_sekarang' => $risk->step_verification,
                    'request_edit_sekarang' => $risk->request_edit,
                    'taksonomi_risiko_id' => $risk->taksonomi_risiko_id,
                    'is_closed' => (bool) $risk->is_closed,
                ];

                if ($this->isAlreadyUnlockedDraft($risk)) {
                    $logger->info("[SUDAH DRAFT UNLOCKED - SKIP] Risiko ID: {$risk->id}", array_merge($context, [
                        'status' => 'skip_already_open',
                        'reason' => 'Risiko sudah berstatus draft (status=1) dan request_edit=2 (unlocked).',
                    ]));
                    $skippedAlreadyOpenCount++;
                    $skippedAlreadyOpenIds[] = $risk->id;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $logger->info("[DRY-RUN PERLU DIBUKA] Risiko ID: {$risk->id}", array_merge($context, [
                        'status' => 'dry_run_need_update',
                        'status_baru' => IdentifikasiRisiko::STATUS_INPUT_DATA,
                        'status_progress_baru' => 1,
                        'step_verification_baru' => 0,
                        'request_edit_baru' => 2,
                    ]));
                    $updatedCount++;
                    $updatedIds[] = $risk->id;
                    $bar->advance();
                    continue;
                }

                try {
                    DB::transaction(function () use ($risk, $logger, $context, &$updatedCount, &$updatedIds) {
                        $oldValues = [
                            'status' => $risk->status,
                            'status_progress' => $risk->status_progress,
                            'step_verification' => $risk->step_verification,
                            'request_edit' => $risk->request_edit,
                        ];

                        $risk->update([
                            'status' => IdentifikasiRisiko::STATUS_INPUT_DATA,
                            'status_progress' => 1,
                            'step_verification' => 0,
                            'request_edit' => 2,
                        ]);

                        $this->resetOrCreateDataBatch($risk);

                        $logger->info("[BERHASIL DIBUKA] Risiko ID: {$risk->id}", array_merge($context, [
                            'status' => 'updated',
                            'old_values' => $oldValues,
                            'new_values' => [
                                'status' => IdentifikasiRisiko::STATUS_INPUT_DATA,
                                'status_progress' => 1,
                                'step_verification' => 0,
                                'request_edit' => 2,
                            ],
                        ]));

                        $updatedCount++;
                        $updatedIds[] = $risk->id;
                    });
                } catch (\Throwable $e) {
                    $logger->error("[GAGAL] Risiko ID: {$risk->id}", array_merge($context, [
                        'status' => 'failed',
                        'error' => $e->getMessage(),
                    ]));
                    $failedCount++;
                    $failedIds[] = $risk->id;
                }

                $bar->advance();
            }
        });

        $bar->finish();

        $logger->info('=== RINGKASAN ===', [
            'dry_run' => $dryRun,
            'dibuka' => $updatedCount,
            'sudah_draft_unlocked_skip' => $skippedAlreadyOpenCount,
            'gagal' => $failedCount,
            'ids_dibuka' => $updatedIds,
            'ids_sudah_draft_unlocked' => $skippedAlreadyOpenIds,
            'ids_gagal' => $failedIds,
        ]);
        $logger->info("=== END OPEN REQUEST EDIT: dibuka {$updatedCount}, skip {$skippedAlreadyOpenCount}, gagal {$failedCount} ===");

        $this->newLine(2);
        $this->info($dryRun ? 'Dry-run selesai!' : 'Proses selesai!');
        $this->line('Dibuka / perlu dibuka : <info>' . $updatedCount . '</info>');
        $this->line('Sudah draft unlocked (skip) : <comment>' . $skippedAlreadyOpenCount . '</comment>');
        if ($failedCount > 0) {
            $this->error("Gagal : {$failedCount}");
        }
        $this->line("Detail log: <comment>storage/logs/{$logFileName}</comment>");

        return $failedCount > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    private function baseQuery(bool $includeClosed, mixed $periodeId, mixed $unitId, string $taksonomiFilter)
    {
        $query = IdentifikasiRisiko::query()
            ->where(function ($q) {
                $q->whereNull('unit_type_id')
                    ->orWhere('unit_type_id', '!=', 4);
            });

        if ($taksonomiFilter === 'empty') {
            $query->where(function ($q) {
                $q->whereNull('taksonomi_risiko_id')
                    ->orWhere('taksonomi_risiko_id', 0);
            });
        }

        if (!$includeClosed) {
            $query->where(function ($q) {
                $q->where('is_closed', false)
                    ->orWhere('is_closed', 0)
                    ->orWhereNull('is_closed');
            });
        }

        if ($periodeId !== null && $periodeId !== '') {
            $query->where('periode_id', $periodeId);
        }

        if ($unitId !== null && $unitId !== '') {
            $query->where('unit_id', $unitId);
        }

        return $query;
    }

    private function isAlreadyUnlockedDraft(IdentifikasiRisiko $risk): bool
    {
        return (int) $risk->status === IdentifikasiRisiko::STATUS_INPUT_DATA
            && (int) $risk->status_progress === 1
            && (int) $risk->step_verification === 0
            && (int) $risk->request_edit === 2;
    }

    private function resetOrCreateDataBatch(IdentifikasiRisiko $risk): void
    {
        $dataBatch = DataBatch::where('unit_id', $risk->unit_id)
            ->where('type', 1)
            ->where('finish', false)
            ->orderBy('batch', 'desc')
            ->first();

        if (!$dataBatch) {
            DataBatch::create([
                'unit_id' => $risk->unit_id,
                'periode_id' => $risk->periode_id,
                'type' => 1,
                'status' => DataBatch::STATUS_PROSES,
                'step_verification' => 0,
                'finish' => false,
            ]);

            return;
        }

        $dataBatch->update([
            'status' => DataBatch::STATUS_PROSES,
            'step_verification' => 0,
        ]);
    }
}
