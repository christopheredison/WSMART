<?php

namespace App\Traits;

trait HasRiskNoteStatuses
{
    public const STATUS_ACCEPTED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_REVISION_SENT = 3;
    public const STATUS_REQUEST_CLOSE = 4;
    public const STATUS_CLOSE_APPROVED = 5;
    public const STATUS_CLOSE_REJECTED = 6;

    public static function statusLabels(): array
    {
        return [
            self::STATUS_ACCEPTED => 'Diterima',
            self::STATUS_REJECTED => 'Ditolak',
            self::STATUS_REVISION_SENT => 'Perbaikan Dikirim',
            self::STATUS_REQUEST_CLOSE => 'Request Penutupan Risiko',
            self::STATUS_CLOSE_APPROVED => 'Terima Penutupan Risiko',
            self::STATUS_CLOSE_REJECTED => 'Tolak Penutupan Risiko',
        ];
    }

    public static function statusLabel(?int $status): string
    {
        return self::statusLabels()[$status] ?? 'Informasi';
    }

    public static function statusBadgeTone(?int $status): string
    {
        return match ((int) $status) {
            self::STATUS_ACCEPTED, self::STATUS_CLOSE_APPROVED => 'success',
            self::STATUS_REJECTED, self::STATUS_CLOSE_REJECTED => 'danger',
            self::STATUS_REVISION_SENT => 'info',
            self::STATUS_REQUEST_CLOSE => 'warning',
            default => 'primary',
        };
    }
}
