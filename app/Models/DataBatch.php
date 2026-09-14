<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBatch extends Model
{
    use HasFactory;

    // Constants untuk status
    const STATUS_PROSES = 1; // Draft
    const STATUS_KIRIM = 2; // Masuk ke Risk Owner Project/Divis
    const STATUS_RANKING = 3;
    const STATUS_VERIFIKASI = 4; // Proses Verifikasi
    const STATUS_REVISI = 5; // Dikembalikan ke Drafter
    const STATUS_UTAMA = 6;
    const STATUS_VERIFIKASI_CORPORATE = 7;
    const STATUS_FINISH = 8;
    const STATUS_REJECTED_FROM_OFFICER_MR = 9; // Dikembalikan dari Risk Officer MR ke Divisi
    const STATUS_REJECTED_FROM_OWNER_MR = 10; // Dikembalikan dari Risk Owner MR ke Risk Officer MR

    // Type batch
    const TYPE_RISK_REGISTER = 1;
    const TYPE_RISK_MONITORING = 2;

    // Kolom lain dari tabel yang dapat diisi
    protected $fillable = [
        'periode_id',
        'type',
        'unit_id',
        'project_id',
        'batch',
        'status',
        'step_verification',
        'finish',
    ];

    /**
     * Approval flow Risk Register Korporat (bisa disesuaikan di sini).
     *
     * Flow default:
     * - Step 0: Risk Officer MR (input + kirim)
     * - Step 1: Risk Owner MR (verifikasi + publish)
     */
    public static function getCorporateApprovalFlow(): array
    {
        return [
            'min_verification' => 1,
            'final_step' => 1,
            'steps' => [
                0 => [
                    'level_id' => 1,
                    'role' => 'RO_MR',
                    'label' => 'Risk Officer MR',
                    'unit_mr' => true,
                    'can_input' => true,
                    'can_send' => true,
                    'can_verify' => false,
                    'can_publish' => false,
                ],
                1 => [
                    'level_id' => 2,
                    'role' => 'RW_MR',
                    'label' => 'Risk Owner MR',
                    'unit_mr' => true,
                    'can_input' => false,
                    'can_send' => false,
                    'can_verify' => true,
                    'can_publish' => true,
                ],
            ],
        ];
    }

    /**
     * Approval flow Monitoring Risiko Korporat (bisa disesuaikan di sini).
     * Status monitoring mengikuti konstanta UnitRiskMonitoring.
     *
     * Flow default:
     * - Status 1 (Draft): Risk Officer MR input
     * - Status 4 (Owner MR): Risk Owner MR verifikasi
     * - Status 100: Published
     */
    public static function getCorporateMonitoringApprovalFlow(): array
    {
        return [
            'final_status' => UnitRiskMonitoring::STATUS_PUBLISHED,
            'steps' => [
                UnitRiskMonitoring::STATUS_DRAFT_REVISI => [
                    'level_id' => 1,
                    'role' => 'RO_MR',
                    'label' => 'Risk Officer MR',
                    'unit_mr' => true,
                    'can_input' => true,
                    'can_send' => true,
                    'next_status' => UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR,
                    'next_label' => 'Kirim ke Risk Owner MR',
                ],
                UnitRiskMonitoring::STATUS_VERIFIKASI_ROW_DIVISI_MR => [
                    'level_id' => 2,
                    'role' => 'RW_MR',
                    'label' => 'Risk Owner MR',
                    'unit_mr' => true,
                    'can_verify' => true,
                    'can_publish' => true,
                    'next_status' => UnitRiskMonitoring::STATUS_PUBLISHED,
                    'next_label' => 'Tetapkan Monitoring',
                ],
            ],
        ];
    }

    /**
     * Resolve step user untuk Risk Register Korporat berdasarkan level.
     */
    public static function resolveCorporateUserStep(int $levelId, bool $isMr = true): array
    {
        $flow = self::getCorporateApprovalFlow();

        foreach ($flow['steps'] as $step => $config) {
            $configUnitMr = (bool) ($config['unit_mr'] ?? false);
            if ((int) $config['level_id'] === (int) $levelId && $configUnitMr === $isMr) {
                return [
                    'u_step' => (int) $step,
                    'user_verification' => $config['label'],
                    'config' => $config,
                    'min_verification' => (int) $flow['min_verification'],
                    'final_step' => (int) $flow['final_step'],
                ];
            }
        }

        $fallback = $flow['steps'][0];

        return [
            'u_step' => 0,
            'user_verification' => $fallback['label'],
            'config' => $fallback,
            'min_verification' => (int) $flow['min_verification'],
            'final_step' => (int) $flow['final_step'],
        ];
    }

    /**
     * Resolve step monitoring korporat berdasarkan level user.
     */
    public static function resolveCorporateMonitoringStep(int $levelId, bool $isMr = true): ?array
    {
        $flow = self::getCorporateMonitoringApprovalFlow();

        foreach ($flow['steps'] as $status => $config) {
            $configUnitMr = (bool) ($config['unit_mr'] ?? false);
            if ((int) $config['level_id'] === (int) $levelId && $configUnitMr === $isMr) {
                return [
                    'status' => (int) $status,
                    'config' => $config,
                    'final_status' => (int) $flow['final_status'],
                ];
            }
        }

        return null;
    }
}
