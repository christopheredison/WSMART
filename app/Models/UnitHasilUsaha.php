<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitHasilUsaha extends Model
{
    use HasFactory;
    
    protected $table = 'unit_hasil_usaha';

    protected $fillable = [
        'unit_id',
        'cost_center',
        'period',
        'kontrak_review',
        'penjualan_ra',
        'penjualan_ri',
        'progress_fisik_ra',
        'progress_fisik_ri',
        'lsp_review',
        'lsp_ra',
        'lsp_ri',
        'lsp_proyeksi',
        'response_data',
    ];
    
    protected $casts = [
        'response_data' => 'array',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}