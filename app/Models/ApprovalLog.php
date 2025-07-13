<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApprovalLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'risk_id',
        'approval_step_id',
        'type',
        'step_order',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    // Relasi ke user yang melakukan approval
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Relasi ke risiko (jika type = 1 untuk divisi/unit)
    public function unitRisk()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risk_id');
    }

    // Relasi ke risiko proyek (jika type = 2 untuk proyek)
    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'risk_id');
    }
    
    // Relasi ke approval step
    public function approvalStep()
    {
        return $this->belongsTo(ApprovalStep::class);
    }
}
