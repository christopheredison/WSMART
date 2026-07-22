<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskSettingChild extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'risk_setting_children';

    public function riskSetting()
    {
        return $this->belongsTo(RiskSetting::class, 'risk_setting_id');
    }
}
