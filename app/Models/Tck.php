<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tck extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'tcks';

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
