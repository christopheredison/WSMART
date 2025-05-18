<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreCriteriaDoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'score_criteria_id',
        'filename',
        'path'
    ];

    public function scoreCriteria()
    {
        return $this->belongsTo(ScoreCriteria::class);
    }
    
}
