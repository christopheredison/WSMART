<?php

namespace App\Traits;

trait HasRequestEditVerification
{
    /**
     * Siapa yang menerima notifikasi dan dapat memverifikasi Request Edit.
     * Ubah daftar ini di model jika penerima perlu diganti.
     *
     * role       : target role (RO_MR, RW_MR, dst)
     * level_id   : level user yang boleh memverifikasi
     * unit_mr    : true jika harus dari divisi MR
     * permission : permission Spatie yang wajib dimiliki
     * label      : teks yang tampil di UI / notifikasi
     */
    public static function getRequestEditVerifiers(?string $scope = null): array
    {
        return [
            [
                'role' => 'RO_MR',
                'level_id' => 1,
                'unit_mr' => true,
                'permission' => 'mr_notification_division',
                'label' => 'Risk Officer MR',
            ],
            [
                'role' => 'RW_MR',
                'level_id' => 2,
                'unit_mr' => true,
                'permission' => 'mr_notification_division',
                'label' => 'Risk Owner MR',
            ],
        ];
    }

    public static function getRequestEditVerifierRoles(?string $scope = null): array
    {
        return array_values(array_unique(array_filter(array_column(static::getRequestEditVerifiers($scope), 'role'))));
    }

    public static function getRequestEditVerifierLabel(?string $scope = null): string
    {
        return collect(static::getRequestEditVerifiers($scope))
            ->pluck('label')
            ->filter()
            ->unique()
            ->implode(' / ');
    }

    public static function canVerifyRequestEdit($user, ?string $scope = null): bool
    {
        return static::resolveRequestEditVerifier($user, $scope) !== null;
    }

    public static function resolveRequestEditVerifierLabel($user, ?string $scope = null): ?string
    {
        $verifier = static::resolveRequestEditVerifier($user, $scope);

        return $verifier['label'] ?? null;
    }

    public static function resolveRequestEditVerifier($user, ?string $scope = null): ?array
    {
        if (!$user) {
            return null;
        }

        $levelId = (int) $user->level_id;
        $isMr = $user->unit ? ((int) $user->unit->unit_mr === 1) : false;

        foreach (static::getRequestEditVerifiers($scope) as $verifier) {
            $matchLevel = (int) ($verifier['level_id'] ?? 0) === $levelId;
            $matchMr = (bool) ($verifier['unit_mr'] ?? false) === $isMr;
            $matchPermission = empty($verifier['permission']) || $user->can($verifier['permission']);

            if ($matchLevel && $matchMr && $matchPermission) {
                return $verifier;
            }
        }

        return null;
    }

    public static function notifyRequestEditVerifiers(string $title, string $message, string $link, string $icon, ?string $scope = null): void
    {
        $users = collect();

        foreach (static::getRequestEditVerifiers($scope) as $verifier) {
            $query = \App\Models\User::query();

            if (!empty($verifier['permission'])) {
                $query->permission($verifier['permission']);
            }

            if (isset($verifier['level_id'])) {
                $query->where('level_id', $verifier['level_id']);
            }

            if (!empty($verifier['unit_mr'])) {
                $query->whereHas('unit', function ($q) {
                    $q->where('unit_mr', 1);
                });
            }

            $users = $users->merge($query->get());
        }

        foreach ($users->unique('id') as $user) {
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'icon' => $icon,
                'link' => $link,
                'read_at' => null,
            ]);
        }
    }
}
