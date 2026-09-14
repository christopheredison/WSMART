<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class FinalRatingPeriod extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'final_rating_id',
        'bobot',
        'score_bobot_konversi',
        'total_score_kinerja',
        'rmi_period_id'
    ];

    public function finalRating()
    {
        return $this->belongsTo(FinalRating::class, 'final_rating_id');
    }

    public function rmiPeriod()
    {
        return $this->belongsTo(RMIPeriod::class, 'rmi_period_id');
    }
}
