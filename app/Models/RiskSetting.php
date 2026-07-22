<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskSetting extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'risk_settings';

    public function child()
    {
        return $this->hasMany(RiskSettingChild::class);
    }
}
