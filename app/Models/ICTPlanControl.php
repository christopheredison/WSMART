<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ICTPlanControl extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ict_plan_controls';

    protected $fillable = [
        'ict_plan_id',
        'key_control_id',
        'key_control'
    ];

    /**
     * Relasi dengan ICTPlan
     */
    public function plan()
    {
        return $this->belongsTo(ICTPlan::class, 'ict_plan_id');
    }

    /**
     * Relasi dengan ICTDo
     */
    public function dos()
    {
        return $this->hasMany(ICTDo::class, 'plan_control_id');
    }
}
