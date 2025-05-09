<?php

namespace App\Supports;

class Helper
{
    public static function syncBuiltInPermissions(): array
    {
        $permissions = config('permission.built_in_permissions');

        $result = [];
        foreach ($permissions as $permission) {
            $result[] = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        return $result;
    }
}