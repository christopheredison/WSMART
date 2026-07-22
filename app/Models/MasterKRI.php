<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterKRI extends Model
{
    use HasFactory;

    protected $table = 'master_kri';

    protected $fillable = [
        'kri',
        'satuan_kri',
        'batas_aman',
        'batas_waspada',
        'batas_bahaya',
        'peristiwa_risiko_id',
        'unit_type_id',
        'jenis', // 1: Unit, 2: Project
    ];

    public const JENIS_UNIT = 1;
    public const JENIS_PROJECT = 2;

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class);
    }

    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }
}
