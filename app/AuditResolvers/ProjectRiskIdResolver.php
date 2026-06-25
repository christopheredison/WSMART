<?php

namespace App\AuditResolvers;

use App\Models\DampakRisikoProject;
use App\Models\KRIProject;
use App\Models\PenyebabRisikoProject;
use App\Models\ProjectRisk;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class ProjectRiskIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?int
    {
        if ($auditable instanceof ProjectRisk) {
            return $auditable->getKey();
        }

        if (
            $auditable instanceof DampakRisikoProject
            || $auditable instanceof PenyebabRisikoProject
            || $auditable instanceof KRIProject
        ) {
            $risikoId = $auditable->getAttribute('risiko_id');

            return $risikoId ? (int) $risikoId : null;
        }

        return null;
    }
}
