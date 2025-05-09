<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterCriteria extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'parameter_criterias';

    protected $fillable = [
        'parameter_id',
        'criteria_statement',//otomatis diisi dari statement parameter plus 001, 002, dst
        'min_score',
        'max_score',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    /**
     * Mendapatkan parameter yang terkait dengan kriteria ini
     */
    public function parameter()
    {
        return $this->belongsTo(MeasurementParameter::class, 'parameter_id');
    }

    /**
     * Mendapatkan semua periode parameter kriteria yang menggunakan kriteria ini
     */
    public function periodParameterCriteria()
    {
        return $this->hasMany(PeriodParameterCriteria::class, 'criteria_id');
    }

    /**
     * Mendapatkan semua detail (level) untuk kriteria ini
     */
    public function details()
    {
        return $this->hasMany(ParameterCriteriaDetail::class, 'parameter_criteria_id');
    }
}
