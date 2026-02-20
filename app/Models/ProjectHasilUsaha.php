<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectHasilUsaha extends Model
{
    use HasFactory;

    protected $table = 'project_hasil_usaha';

    protected $fillable = [
        'project_id',
        'profit_center',
        'period',
        'kontrak_review_total',
        'kontrak_review',
        'progress_fisik_ra',
        'progress_fisik_ri',
        'penjualan_ra',
        'penjualan_ri',
        'lsp_review',
        'lsp_ra',
        'lsp_ri',
        'lsp_proyeksi',
        'response_data',
    ];

    protected $casts = [
        'response_data' => 'array',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
