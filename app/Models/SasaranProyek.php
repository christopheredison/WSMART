<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SasaranProyek extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'costcenter_code',
        'kpi_desc',
        'status',
        'tahun',
        'kpi_id',
        'target_akhir_tahun',
        'satuan'
    ];
}
