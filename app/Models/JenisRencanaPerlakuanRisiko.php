<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisRencanaPerlakuanRisiko extends Model
{
    use HasFactory;

    protected $fillable = [
        'jenis_rencana_perlakuan_risiko',
    ];
}
