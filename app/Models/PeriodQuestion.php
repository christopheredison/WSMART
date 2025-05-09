<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodQuestion extends Model
{
    use HasFactory;
    protected $fillable = [
        'period_id',
        'question_id',
    ];

    public function rmiPeriod()
    {
        return $this->belongsTo(RMIPeriod::class, 'period_id');
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'period_question_id');
    }
}
