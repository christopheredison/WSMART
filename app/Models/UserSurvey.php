<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kuesioner_responden_id',
        'period_id', 
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kuesionerResponden() 
    {
        return $this->belongsTo(KuesionerResponden::class, 'kuesioner_responden_id');
    }

    public function period() 
    {
        return $this->belongsTo(RMIPeriod::class, 'period_id');
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class);
    }
}
