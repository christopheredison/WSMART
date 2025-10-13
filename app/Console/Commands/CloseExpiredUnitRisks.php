<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unit;
use App\Models\IdentifikasiRisiko;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CloseExpiredUnitRisks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'risks:close-expired-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menutup risiko (is_closed=1) untuk unit yang sudah tidak valid berdasarkan valid_to';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = Carbon::today();

        // Ambil unit yang valid_to tidak null dan kurang dari hari ini
        $expiredUnits = Unit::query()
            ->whereNotNull('valid_to')
            ->whereDate('valid_to', '<', $today->toDateString())
            ->pluck('id');

        if ($expiredUnits->isEmpty()) {
            $this->info('Tidak ada unit yang expired hari ini.');
            Log::channel('closed_risk_log')->info('[Scheduler] Tidak ada unit expired hari ini.');
            return self::SUCCESS;
        }

        // Update risiko yang terkait unit expired menjadi closed
        $updated = IdentifikasiRisiko::query()
            ->whereIn('unit_id', $expiredUnits)
            ->where('is_closed', false)
            ->update(['is_closed' => true]);

        $message = sprintf('Risiko ditutup: %d (unit expired: %d)', $updated, $expiredUnits->count());
        $this->info($message);
        Log::channel('closed_risk_log')->info('[Scheduler] '.$message);
        return self::SUCCESS;
    }
}