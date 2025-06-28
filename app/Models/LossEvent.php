<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEvent extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'loss_events';

    public function child()
    {
        return $this->hasMany(LossEventChild::class);
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function files()
    {
        return $this->hasMany(LossEventFile::class);
    }
    
    public function kategoriKejadian()
    {
        return $this->belongsTo(KategoriKejadian::class, 'kategori_kejadian_id');
    }
}
