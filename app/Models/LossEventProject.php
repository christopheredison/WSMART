<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LossEventProject extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'tahun',
        'deskripsi_kejadian',
        'jumlah_kejadian',
        'peristiwa_risiko_id',
        'project_sektor_id',
        'project_id',
        'tanggal_kejadian',
        'rentang_kejadian_awal',
        'rentang_kejadian_akhir',
        'nilai_kerugian_finansial',
        'nilai_kerugian_non_finansial',
        'unit_penanggung_jawab',
    ];

    public function projectSektor()
    {
        return $this->belongsTo(ProjectSektor::class, 'project_sektor_id');
    }

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
}
