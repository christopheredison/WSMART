<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ScoreCriteria extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    // jika Eloquent tidak otomatis mengenali table name:
    protected $table = 'score_criterias';

    protected $fillable = [
        'parameter_criteria_id',
        'period_id',
        'score',
        'gap_analysis'
    ];

    public function parameterCriteria()
    {
        return $this->belongsTo(ParameterCriteria::class, 'parameter_criteria_id');
    }

    public function period()
    {
        return $this->belongsTo(RMIPeriod::class, 'period_id');
    }

    public function documents()
    {
        return $this->hasMany(ScoreCriteriaDoc::class);
    }
}
