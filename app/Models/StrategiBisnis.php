<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StrategiBisnis extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sasaran_id',
        'strategi',
        'status',
    ];

    public function sasaran()
    {
        return $this->belongsTo(Sasaran::class);
    }
}
