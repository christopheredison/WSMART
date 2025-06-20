<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectKontrolEksisting extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_risk_id',
        'kontrol_eksisting_desc',
    ];

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class);
    }
}
