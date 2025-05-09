<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScoreParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'period_id',
        'sub_dimension_id',
        'parameter_id',
        'score',
        'score_parameter_desc',
        'parameter_wawancara'// score = 1 dan 2 -> Prioritas Tinggi, 3 -> Prioritas Menengah, 4 -> Prioritas Rendah, 5 -> Tidak Wawancara
    ];

    public function period()
    {
        return $this->belongsTo(RMIPeriod::class, 'period_id');
    }

    public function subDimension()
    {
        return $this->belongsTo(SubDimension::class, 'sub_dimension_id');
    }

    public function parameter()
    {
        return $this->belongsTo(MeasurementParameter::class, 'parameter_id');
    }
}
