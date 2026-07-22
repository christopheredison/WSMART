<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetailPenilaianCapaianKinerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['penilaian_capaian_kinerja_id','parameter_id','option_id','comment'];

    public function penilaian()
    {
        return $this->belongsTo(PenilaianCapaianKinerja::class, 'penilaian_capaian_kinerja_id');
    }

    public function parameter()
    {
        return $this->belongsTo(ParameterKinerja::class, 'parameter_id');
    }

    public function pilihan()
    {
        return $this->belongsTo(PilihanParameterKinerja::class, 'option_id');
    }
}
