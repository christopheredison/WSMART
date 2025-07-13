<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalStep extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'approval_flow_id',
        'level_id',
        'step_order',
    ];

    // Relasi ke approval flow
    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    // Relasi ke level
    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    // Relasi ke approval logs
    public function approvalLogs()
    {
        return $this->hasMany(ApprovalLog::class);
    }
}
