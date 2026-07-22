<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SubDimension extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['dimension_id', 'name'];

    public function dimension()
    {
        return $this->belongsTo(Dimension::class);
    }

    public function measurementParameters()
    {
        return $this->hasMany(MeasurementParameter::class);
    }
}
