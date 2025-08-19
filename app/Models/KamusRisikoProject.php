<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KamusRisikoProject extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_risk_id',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class);
    }
}