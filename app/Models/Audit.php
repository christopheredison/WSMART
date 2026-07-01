<?php

namespace App\Models;

use OwenIt\Auditing\Models\Audit as OwenItAudit;

class Audit extends OwenItAudit
{
    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'identifikasi_risiko_id');
    }

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'project_risk_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function getEntityLabelAttribute(): string
    {
        return match ($this->auditable_type) {
            IdentifikasiRisiko::class => 'Identifikasi Risiko',
            DampakRisikoUnit::class => 'Dampak Risiko',
            PenyebabRisiko::class => 'Penyebab Risiko',
            KRI::class => 'Key Risk Indicator (KRI)',
            ProjectRisk::class => 'Risiko Proyek',
            DampakRisikoProject::class => 'Dampak Risiko Proyek',
            PenyebabRisikoProject::class => 'Penyebab Risiko Proyek',
            KRIProject::class => 'Key Risk Indicator Proyek (KRI)',
            Unit::class => 'Unit',
            default => class_basename($this->auditable_type),
        };
    }

    public function getEventLabelAttribute(): string
    {
        return match ($this->event) {
            'created' => 'Ditambahkan',
            'updated' => 'Diperbarui',
            'deleted' => 'Dihapus',
            'restored' => 'Dipulihkan',
            default => $this->event,
        };
    }
}
