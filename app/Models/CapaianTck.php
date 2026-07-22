<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CapaianTck extends Model
{
    protected $fillable = [
        'unit_id',
        'periode_id',
        'capaian',
        'tanggal_data'
    ];

    protected $casts = [
        'tanggal_data' => 'date:Y-m-d',
        'capaian' => 'decimal:2',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
