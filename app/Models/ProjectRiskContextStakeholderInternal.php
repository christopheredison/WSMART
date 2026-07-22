<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskContextStakeholderInternal extends Model
{
    use HasFactory;

    protected $table = 'project_risk_context_stakeholder_internals';

    protected $fillable = [
        'project_risk_context_id',
        'stakeholder',
        'peran',
        'komunikasi',
    ];

    public function projectRiskContext()
    {
        return $this->belongsTo(ProjectRiskContext::class, 'project_risk_context_id');
    }
}
