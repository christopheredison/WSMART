<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectRiskContextMember extends Model
{
    use HasFactory;

    protected $table = 'project_risk_context_members';

    protected $fillable = [
        'project_risk_context_id',
        'nama',
        'jabatan_id',
    ];

    public function projectRiskContext()
    {
        return $this->belongsTo(ProjectRiskContext::class, 'project_risk_context_id');
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
