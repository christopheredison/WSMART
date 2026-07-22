<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KamusRisikoAp extends Model
{
    use HasFactory;

    protected $fillable = [
        'risiko_id',
    ];

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }
}
