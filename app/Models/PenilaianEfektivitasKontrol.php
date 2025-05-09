<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenilaianEfektivitasKontrol extends Model
{
    use HasFactory;

    protected $fillable = [
        'efektivitas_kontrol',
    ];
}
