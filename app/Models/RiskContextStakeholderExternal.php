<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskContextStakeholderExternal extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_context_id',
        'stakeholder',
        'peran',
        'komunikasi',
    ];

    public function riskContext()
    {
        return $this->belongsTo(RiskContext::class);
    }
}
