<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringRisikoFile extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'monitoring_risiko_files';

    public function monitoringRisiko()
    {
        return $this->belongsTo(MonitoringRisiko::class, 'monitoring_risiko_id');
    }
}
