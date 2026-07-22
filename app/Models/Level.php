<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function jabatans()
    {
        return $this->belongsToMany(Jabatan::class, 'jabatan_level');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'level_role');
    }
}
