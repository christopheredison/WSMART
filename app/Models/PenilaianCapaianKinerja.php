<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PenilaianCapaianKinerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id','rmi_period_id', 'total_nilai_capaian_kinerja', 'capaian_kinerja', 'total_nilai_kpmr', 'kpmr'];
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function period()
    {
        return $this->belongsTo(RMIPeriod::class, 'rmi_period_id');
    }

    public function details()
    {
        return $this->hasMany(DetailPenilaianCapaianKinerja::class, 'penilaian_capaian_kinerja_id');
    }
}
