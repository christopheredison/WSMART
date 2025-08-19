<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyebabRisikoProjectLed extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_event_project_id',
        'penyebab_risiko',
    ];

    public function led()
    {
        return $this->belongsTo(LossEventProject::class, 'loss_event_project_id');
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoProjectLed::class, 'penyebab_risiko_led_id');
    }
}
