<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Periode extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'periodes';

    protected $fillable = [
        'tahun',
        'status',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'non-active';

    public function ambangBatasRisiko()
    {
        return $this->hasOne(AmbangBatasRisiko::class);
    }

    public function riskLimitPeriodes()
    {
        return $this->hasMany(RiskLimitPeriode::class);
    }
}
