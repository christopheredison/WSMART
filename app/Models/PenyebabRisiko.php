<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenyebabRisiko extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'penyebab_risikos';

    public function identifikasiRisiko()
    {
        return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    }

    public function perlakuanPenyebabRisiko()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id');
    }

    public function perlakuanPenyebabRisikoUnit()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'penyebab_risiko_id');
    }
}
