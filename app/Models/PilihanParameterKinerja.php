<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PilihanParameterKinerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['parameter_id','code','description','scale', 'score'];

    public function parameter()
    {
        return $this->belongsTo(ParameterKinerja::class, 'parameter_id');
    }
}
