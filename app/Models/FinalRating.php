<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinalRating extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'rating',
        'conversion_score'
    ];

    public function finalRatingPeriods()
    {
        return $this->hasMany(FinalRatingPeriod::class, 'final_rating_id');
    }
}
