<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyebabRisikoUnitLed extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_event_unit_id',
        'penyebab_risiko',
    ];
    
    public function led()
    {
        return $this->belongsTo(LossEvent::class, 'loss_event_unit_id');
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnitLed::class, 'penyebab_risiko_led_id');
    }
}
