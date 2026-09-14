<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ScoreCriteriaDoc extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

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
