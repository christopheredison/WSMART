<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KRIProjectMonitoring extends Model
{
    use HasFactory;

    protected $fillable = [
        'kri_project_id',
        'project_monitoring_id',
        'status_kri_terkini',
        'nilai_kri_terkini',
    ];

    protected $casts = [
        'status_kri_terkini' => 'integer',
    ];

    protected $with = ['projectMonitoring'];

    public const STATUS_AMAN = 1;
    public const STATUS_WASPADA = 2;
    public const STATUS_BAHAYA = 3;

    public function kriProject()
    {
        return $this->belongsTo(KRIProject::class, 'kri_project_id');
    }

    public function projectMonitoring()
    {
        return $this->belongsTo(ProjectRiskMonitoring::class, 'project_monitoring_id');
    }
}
