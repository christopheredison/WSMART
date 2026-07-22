<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonitoringRisiko extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'monitoring_risikos';

    public function rencanaPerlakuanRisiko()
    {
        return $this->belongsTo(RencanaPerlakuanRisiko::class, 'rencana_perlakuan_risiko_id');
    }

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function files()
    {
        return $this->hasMany(MonitoringRisikoFile::class);
    }

}
