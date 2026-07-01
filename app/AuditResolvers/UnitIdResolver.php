<?php

namespace App\AuditResolvers;

use App\Models\Unit;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class UnitIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?int
    {
        if ($auditable instanceof Unit) {
            return $auditable->getKey();
        }

        return null;
    }
}
