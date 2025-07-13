<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalFlow extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'project_id',
        'min_verification',
    ];

    // Relasi ke unit
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // Relasi ke project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Relasi ke approval steps
    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class);
    }
}
