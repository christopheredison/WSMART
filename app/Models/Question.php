<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'parameter_id',
        'group_id',
        'question',
    ];

    public function measurementParameter()
    {
        return $this->belongsTo(MeasurementParameter::class, 'parameter_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function answerChoices()
    {
        return $this->hasMany(AnswerChoice::class);
    }
}
