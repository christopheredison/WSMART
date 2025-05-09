<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'jenis_risikos';

    public function kategori()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }
}
