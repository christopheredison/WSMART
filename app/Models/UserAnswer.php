<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'period_question_id',
        'user_survey_id',
        'answer_choice_id',
        'level',
        'user_id',
        'kuesioner_responden_id',
        'question_notes',
        'answer_notes',
    ];

    public function periodQuestion()
    {
        return $this->belongsTo(PeriodQuestion::class, 'period_question_id');
    }

    public function answerChoice()
    {
        return $this->belongsTo(AnswerChoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kuesionerResponden()
    {
        return $this->belongsTo(KuesionerResponden::class, 'kuesioner_responden_id');
    }
}
