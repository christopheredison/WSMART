<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeriodParameterCriteria extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'period_parameter_criteria';

    protected $fillable = [
        'parameter_id',
        'criteria_id'
    ];

    /**
     * Mendapatkan parameter yang terkait
     */
    public function parameter()
    {
        return $this->belongsTo(MeasurementParameter::class, 'parameter_id');
    }

    /**
     * Mendapatkan kriteria yang terkait
     */
    public function criteria()
    {
        return $this->belongsTo(ParameterCriteria::class, 'criteria_id');
    }
}
