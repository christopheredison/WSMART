<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisProgramDalamRKAP extends Model
{
    use HasFactory;

    protected $table = 'jenis_program_dalam_rkap';

    protected $fillable = [
        'jenis_program_rkap',
    ];

    public function perlakuanPenyebabMonitorings()
    {
        return $this->hasMany(PerlakuanPenyebabMonitoring::class, 'jenis_program_rkap_id');
    }
}
