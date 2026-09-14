<?php

namespace App\AuditResolvers;

use App\Models\ParameterKinerjaDocument;
use App\Models\PenilaianCapaianKinerja;
use App\Models\DetailPenilaianCapaianKinerja;
use App\Models\RMIPeriod;
use App\Models\ScoreCriteria;
use App\Models\ScoreCriteriaDoc;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Contracts\Resolver;

class RmiPeriodIdResolver implements Resolver
{
    public static function resolve(Auditable $auditable = null): ?int
    {
        if (!$auditable) {
            return null;
        }

        if ($auditable instanceof RMIPeriod) {
            return (int) $auditable->getKey();
        }

        if (!empty($auditable->rmi_period_id)) {
            return (int) $auditable->rmi_period_id;
        }

        if (!empty($auditable->period_id)) {
            return (int) $auditable->period_id;
        }

        if ($auditable instanceof DetailPenilaianCapaianKinerja || $auditable instanceof ParameterKinerjaDocument) {
            $penilaianId = $auditable->penilaian_capaian_kinerja_id ?? null;
            if ($penilaianId) {
                $periodId = PenilaianCapaianKinerja::withTrashed()->whereKey($penilaianId)->value('rmi_period_id');
                return $periodId ? (int) $periodId : null;
            }
        }

        if ($auditable instanceof ScoreCriteriaDoc) {
            $criteriaId = $auditable->score_criteria_id ?? null;
            if ($criteriaId) {
                $periodId = ScoreCriteria::withTrashed()->whereKey($criteriaId)->value('period_id');
                return $periodId ? (int) $periodId : null;
            }
        }

        return null;
    }
}
