<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskMonitoringNote extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $table = 'risk_monitoring_notes';

    protected $fillable = [
        'risiko_id',
        'type',
        'status',
        'notes',
        'user_id',
        'quarter',
        'month',
        'year',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class, 'risiko_id');
    }

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }
}