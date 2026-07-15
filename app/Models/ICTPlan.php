<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ICTPlan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ict_plans';

    protected $fillable = [
        'sasaran_bumn',
        'risiko_id',
        'peristiwa_risiko',
        'lokasi_risiko',
        'type',
        'business_process',
        'metode_pengujian',
        'status',
        'rejection_reason',
        'tahun_pelaporan',
    ];

    /**
     * Relasi dengan ICTPlanControl
     */
    public function planControls()
    {
        return $this->hasMany(ICTPlanControl::class, 'ict_plan_id');
    }

    /**
     * Relasi dengan ICTReport
     */
    public function reports()
    {
        return $this->hasMany(ICTReport::class, 'ict_plan_id');
    }
}
