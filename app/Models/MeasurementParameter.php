<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeasurementParameter extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'sub_dimension_id',
        'statement',
    ];

    public function subDimension()
    {
        return $this->belongsTo(SubDimension::class);
    }

    /**
     * Mendapatkan semua kriteria untuk parameter ini
     */
    public function criteria()
    {
        return $this->hasMany(ParameterCriteria::class, 'parameter_id');
    }
    
    /**
     * Mendapatkan semua periode parameter kriteria untuk parameter ini
     */
    public function periodParameterCriteria()
    {
        return $this->hasMany(PeriodParameterCriteria::class, 'parameter_id');
    }
}
