<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiskContextMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'risk_context_id',
        'nama',
        'jabatan_id',
    ];

    public function riskContext()
    {
        return $this->belongsTo(RiskContext::class);
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }
}
