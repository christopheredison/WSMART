<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventAp extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'loss_event_aps';

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function kategoriKejadian()
    {
        return $this->belongsTo(KategoriKejadian::class, 'kategori_kejadian_id');
    }

    public function penyebabRisikoLeds()
    {
        return $this->hasMany(PenyebabRisikoApLed::class, 'loss_event_ap_id');
    }
}
