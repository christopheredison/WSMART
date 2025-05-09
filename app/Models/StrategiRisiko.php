<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategiRisiko extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'strategi_risikos';

    public function riskLimit()
    {
        return $this->hasMany(RiskLimit::class, 'strategi_risiko_id');
    }
}
