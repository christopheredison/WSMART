<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ICTReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ict_reports';

    protected $fillable = [
        'ict_plan_id',
        'status_tindak_lanjut',
        'keterangan',
        'realisasi_tindak_lanjut',
    ];

    /**
     * Relasi dengan ICTPlan
     */
    public function plan()
    {
        return $this->belongsTo(ICTPlan::class, 'ict_plan_id');
    }
}
