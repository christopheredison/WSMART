<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreCriteria extends Model
{
    use HasFactory, SoftDeletes;

    // jika Eloquent tidak otomatis mengenali table name:
    protected $table = 'score_criterias';

    protected $fillable = [
        'parameter_criteria_id',
        'period_id',
        'score',
    ];

    public function parameterCriteria()
    {
        return $this->belongsTo(ParameterCriteria::class, 'parameter_criteria_id');
    }

    public function period()
    {
        return $this->belongsTo(RMIPeriod::class, 'period_id');
    }
}
