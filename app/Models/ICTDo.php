<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ICTDo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'ict_dos';

    protected $fillable = [
        'plan_control_id',
        'jenis_kontrol',
        'bentuk_kontrol',
        'level_pengendalian',
        'kecukupan_desain_pengendalian_1',
        'kecukupan_desain_pengendalian_2',
        'kecukupan_desain_pengendalian_3',
        'kecukupan_desain_pengendalian_4',
        'kecukupan_desain_pengendalian_akhir',
        'efektivitas_desain_pengendalian_1',
        'efektivitas_desain_pengendalian_2',
        'efektivitas_desain_pengendalian_3',
        'efektivitas_desain_pengendalian_4',
        'efektivitas_desain_pengendalian_akhir',
        'kesimpulan_akhir',
        'hasil_temuan',
        'rencana_tindak_lanjut',
        'batas_waktu_penyelesaian',
        'penanggung_jawab'
    ];

    protected $casts = [
        'batas_waktu_penyelesaian' => 'date'
    ];

    /**
     * Relasi dengan ICTPlanControl
     */
    public function planControl()
    {
        return $this->belongsTo(ICTPlanControl::class, 'plan_control_id');
    }
}
