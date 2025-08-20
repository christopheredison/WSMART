<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyebabRisikoApLed extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_event_ap_id',
        'penyebab_risiko',
    ];
    
    public function led()
    {
        return $this->belongsTo(LossEventAp::class, 'loss_event_ap_id');
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoApLed::class, 'penyebab_risiko_led_id');
    }
}
