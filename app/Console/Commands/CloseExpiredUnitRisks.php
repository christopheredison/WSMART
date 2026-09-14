<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Unit;
use App\Models\IdentifikasiRisiko;
use App\Models\Notification; // added
use App\Models\User; // added
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Supports\ApiHC;

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
        $updatedRisks = IdentifikasiRisiko::query()
            ->whereIn('unit_id', $expiredUnits)
            ->where('is_closed', false)
            ->update([
                'is_closed' => true,
                'closed_at' => now(),
            ]);

        // Update status unit yang expired menjadi 0 (false)
        $updatedUnits = Unit::query()
            ->whereIn('id', $expiredUnits)
            ->update(['status' => false]);

        // Buat notifikasi untuk user terkait unit expired dan admin dengan permission get_all_notification
        $expiredUnitModels = Unit::whereIn('id', $expiredUnits)->get();

        // Ambil user admin yang memiliki permission get_all_notification
        $adminUsers = User::permission('get_all_notification')->get();

        // Sync ulang unit
        try {
            Unit::sync();
            Log::channel('closed_risk_log')->info('[Scheduler] Unit sync selesai');
        } catch (\Exception $e) {
            Log::channel('closed_risk_log')->error('[Scheduler] Unit sync gagal', [
                'error' => $e->getMessage(),
            ]);
        }
        // End sync ulang unit
        
        // Inisialisasi client API HC sekali
        $apiHC = new ApiHC();

        foreach ($expiredUnitModels as $unit) {
            // Notifikasi ke semua user yang terkait dengan unit ini
            $unitUsers = User::where('unit_id', $unit->id)->get();

            // Sinkronisasi unit user berdasarkan NIK/NIP via API HC (cost_center_parent)
            foreach ($unitUsers as $user) {
                $identifier = $user->nip ?: ($user->nik ?: null);
                if (!$identifier) {
                    Log::channel('closed_risk_log')->warning('[Scheduler] User tanpa NIK/NIP, lewati sinkronisasi', [
                        'user_id' => $user->id,
                        'unit_id' => $unit->id,
                    ]);
                    // tetap kirim notifikasi di bawah
                } else {
                    try {
                        $response = $apiHC->apiRequest('GET', '/', [
                            'method' => 'get_pegawai',
                            'nip' => $identifier,
                        ]);
                        $ccParent = $response['data'][0]['cost_center_parent'] ?? null;
                        if ($ccParent) {
                            $newUnit = Unit::where('cost_center', $ccParent)->first();
                            if ($newUnit && $newUnit->id !== $user->unit_id) {
                                $user->update([
                                    'unit_id' => $newUnit->id,
                                    'unit_type_id' => $newUnit->unit_type_id ?? $user->unit_type_id,
                                ]);
                                Log::channel('closed_risk_log')->info('[Scheduler] Unit user diperbarui via API HC', [
                                    'user_id' => $user->id,
                                    'old_unit_id' => $unit->id,
                                    'new_unit_id' => $newUnit->id,
                                    'cost_center_parent' => $ccParent,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::channel('closed_risk_log')->error('[Scheduler] Gagal sinkronisasi user via API HC', [
                            'user_id' => $user->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                Notification::create([
                    'user_id' => $user->id,
                    'title' => 'Unit invalid & risiko ditutup',
                    'message' => sprintf(
                        'Unit "%s" telah invalid (expired %s). Semua risiko pada unit ini ditutup otomatis.',
                        $unit->name,
                        optional($unit->valid_to)->format('Y-m-d')
                    ),
                    'icon' => '⚠️',
                    'link' => '/risk-register-unit?unit_id=' . $unit->id,
                ]);
            }

            // Notifikasi ke admin (get_all_notification) untuk setiap unit invalid
            foreach ($adminUsers as $admin) {
                Notification::create([
                    'user_id' => $admin->id,
                    'title' => 'Unit invalid terdeteksi',
                    'message' => sprintf(
                        'Unit "%s" telah invalid (expired %s). Sistem menutup otomatis semua risiko unit tersebut.',
                        $unit->name,
                        optional($unit->valid_to)->format('Y-m-d')
                    ),
                    'icon' => '🔔',
                    'link' => '/risk-register-unit?unit_id=' . $unit->id,
                ]);
            }
        }

        $message = sprintf(
            'Risiko ditutup: %d (unit expired: %d, status diupdate: %d)',
            $updatedRisks,
            $expiredUnits->count(),
            $updatedUnits
        );
        $this->info($message);
        Log::channel('closed_risk_log')->info('[Scheduler] ' . $message);
        return self::SUCCESS;
    }
}