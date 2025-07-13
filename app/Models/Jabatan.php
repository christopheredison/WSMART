<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    public function levels()
    {
        return $this->belongsToMany(Level::class, 'jabatan_level');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function roles()
    {
        return $this->hasManyThrough(
            Role::class,
            Level::class,
            'jabatan_level', // Tabel pivot antara jabatan dan level
            'level_role',    // Tabel pivot antara level dan role
            'id',            // Kunci lokal pada jabatan
            'id'             // Kunci lokal pada level
        );
    }

    public function perlakuanPenyebabRisikoUnits()
    {
        return $this->hasMany(PerlakuanPenyebabRisikoUnit::class, 'pic_jabatan_id');
    }

    public function perlakuanPenyebabRisikos()
    {
        return $this->hasMany(PerlakuanPenyebabRisiko::class, 'pic_jabatan_id');
    }

    public function lossEventProjects()
    {
        return $this->hasMany(LossEventProject::class, 'unit_penanggung_jawab_jabatan_id');
    }

    public function lossEvents()
    {
        return $this->hasMany(LossEvent::class, 'unit_penanggung_jawab_jabatan_id');
    }
    
    public function ictDos()
    {
        return $this->hasMany(ICTDo::class, 'penanggung_jawab_jabatan_id');
    }
}
