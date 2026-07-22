<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterCriteriaDetail extends Model
{
    use HasFactory, SoftDeletes;

    // Jika Eloquent tidak otomatis men-generate table name:
    // protected $table = 'parameter_criteria_details';

    protected $fillable = [
        'parameter_criteria_id',
        'criteria',
        'level',
    ];

    /**
     * Relasi ke parent ParameterCriteria
     */
    public function parameterCriteria()
    {
        return $this->belongsTo(ParameterCriteria::class, 'parameter_criteria_id');
    }
}
