<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerlakuanDampakRisikoUnit extends Model
{
    protected $fillable = [
        'risiko_id',
        'dampak_risiko_id',
        'rencana_perlakuan_risiko',
        'output_perlakuan_risiko',
        'biaya_perlakuan_risiko',
        'pic',
        'pic_jabatan_id',
        'divisi_terkait',
        'timeline_perlakuan_risiko_start',
        'timeline_perlakuan_risiko_end',
        'opsi_perlakuan_risiko'
    ];

    protected $casts = [
        'timeline_perlakuan_risiko_start' => 'date',
        'timeline_perlakuan_risiko_end' => 'date',
        'divisi_terkait' => 'array',
    ];

    public function risiko() {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function dampakRisikoUnit()
    {
        return $this->belongsTo(DampakRisikoUnit::class, 'dampak_risiko_id', 'id');
    }

    public function picJabatan()
    {
        return $this->belongsTo(Jabatan::class, 'pic_jabatan_id');
    }

    public function opsiPerlakuan() {
        return $this->belongsTo(OpsiPerlakuanRisiko::class, 'opsi_perlakuan_risiko');
    }

    public function getWaktuPerlakuanRisikoAttribute() {
        if (!$this->timeline_perlakuan_risiko_start) return '-';
        $start = $this->timeline_perlakuan_risiko_start->format('d M Y');
        if ($this->timeline_perlakuan_risiko_end && $this->timeline_perlakuan_risiko_start->format('Y-m-d') !== $this->timeline_perlakuan_risiko_end->format('Y-m-d')) {
            return $start . ' - ' . $this->timeline_perlakuan_risiko_end->format('d M Y');
        }
        return $start;
    }

    public function getDivisiTerkaitUnitsAttribute() {
        return empty($this->divisi_terkait) ? collect() : Unit::whereIn('id', $this->divisi_terkait)->get();
    }

    public function perlakuanDampakMonitorings() {
        return $this->hasMany(PerlakuanDampakMonitoringUnit::class, 'perlakuan_dampak_id');
    }

    public function lastMonitoring() {
        return $this->hasOne(PerlakuanDampakMonitoringUnit::class, 'perlakuan_dampak_id')->orderBy('id', 'desc');
    }
}
