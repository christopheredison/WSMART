<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerlakuanPenyebabRisikoUnitLed extends Model
{
    use HasFactory;

    protected $fillable = [
        'penyebab_risiko_led_id',
        'rencana_perlakuan_risiko',
        'output_perlakuan_risiko',
        'biaya_perlakuan_risiko',
        'pic',
        'pic_jabatan_id',
        'timeline_perlakuan_risiko_start',
        'timeline_perlakuan_risiko_end',
        'opsi_perlakuan_risiko',
        'jenis_rencana_perlakuan_risiko',
    ];

    protected $casts = [
        'timeline_perlakuan_risiko_start' => 'date',
        'timeline_perlakuan_risiko_end' => 'date',
    ];
    
    public function penyebabRisikoUnitLed()
    {
        return $this->belongsTo(PenyebabRisikoUnitLed::class, 'penyebab_risiko_led_id');
    }

    public function getWaktuPerlakuanRisikoAttribute()
    {
        if (!$this->timeline_perlakuan_risiko_start) {
            return '-';
        }

        $start = $this->timeline_perlakuan_risiko_start->format('d M Y');
        
        if ($this->timeline_perlakuan_risiko_end && 
            $this->timeline_perlakuan_risiko_start->format('Y-m-d') === $this->timeline_perlakuan_risiko_end->format('Y-m-d')) {
            return $start;
        }

        // Jika ada end date, tampilkan range
        if ($this->timeline_perlakuan_risiko_end) {
            return $start . ' - ' . $this->timeline_perlakuan_risiko_end->format('d M Y');
        }

        return $start;
    }

    public function picJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'pic_jabatan_id');
    }
}
