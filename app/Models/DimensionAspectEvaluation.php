<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class DimensionAspectEvaluation extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    protected $fillable = [
        'period_id',
        'sub_dimension_id',
        'dimension_id',
        'score_dimension',
        'score_dimension_desc',
    ];

    public function subDimension()
    {
        return $this->belongsTo(SubDimension::class, 'sub_dimension_id');
    }
    
    public function dimension()
    {
        return $this->belongsTo(Dimension::class, 'dimension_id');
    }
}
