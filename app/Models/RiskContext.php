<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskContext extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'unit_id',
        'periode_id',
        'nilai',
        'pimpinan_tertinggi_jabatan_id',
        'sponsor',
        'deskripsi',
        'tujuan',
        'lingkup_pekerjaan',
        'pekerjaan_luar_lingkup',
        'sasaran',
        'batasan',
        'asumsi_dasar',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }

    public function pimpinanTertinggi()
    {
        return $this->belongsTo(Jabatan::class, 'pimpinan_tertinggi_jabatan_id');
    }

    public function members()
    {
        return $this->hasMany(RiskContextMember::class);
    }

    public function stakeholderInternals()
    {
        return $this->hasMany(RiskContextStakeholderInternal::class);
    }

    public function stakeholderExternals()
    {
        return $this->hasMany(RiskContextStakeholderExternal::class);
    }
}
