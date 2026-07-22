<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterKinerjaDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'penilaian_capaian_kinerja_id',
        'parameter_id',
        'filename',
        'file_path',
        'mimetype',
        'description',
    ];

    public function penilaian()
    {
        return $this->belongsTo(PenilaianCapaianKinerja::class, 'penilaian_capaian_kinerja_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ParameterKinerja::class, 'parameter_id');
    }
}