<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RencanaPerlakuanRisiko extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'rencana_perlakuan_risikos';

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function getRreAttribute()
    {
        return $this->target_skala_risiko_q4 ?: $this->target_skala_risiko_q3 ?: $this->target_skala_risiko_q2 ?: $this->target_skala_risiko_q1;
    }

    public function getTenggatWaktuAttribute()
    {
        $start = null;
        $end = null;

        if ($this->target_skala_risiko_q1) {
            $start = 'Q1';
            $end = 'Q1';
        }

        if ($this->target_skala_risiko_q2) {
            $start = $start ?: 'Q2';
            $end = 'Q2';
        }

        if ($this->target_skala_risiko_q3) {
            $start = $start ?: 'Q3';
            $end = 'Q3';
        }

        if ($this->target_skala_risiko_q4) {
            $start = $start ?: 'Q4';
            $end = 'Q4';
        }

        if (!$start && !$end) {
            return '';
        }

        return $start == $end ? $start : $start . ' - ' . $end;
    }
}
