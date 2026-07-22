<?php

namespace App\AuditResolvers;

use App\Models\DampakRisikoUnit;
use App\Models\IdentifikasiRisiko;
use App\Models\KRI;
use App\Models\PenyebabRisiko;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class IdentifikasiRisikoIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?int
    {
        if ($auditable instanceof IdentifikasiRisiko) {
            return $auditable->getKey();
        }

        if (
            $auditable instanceof DampakRisikoUnit
            || $auditable instanceof PenyebabRisiko
            || $auditable instanceof KRI
        ) {
            $risikoId = $auditable->getAttribute('risiko_id');

            return $risikoId ? (int) $risikoId : null;
        }

        return null;
    }
}
