<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParameterKinerja extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['code','name', 'type', 'weight','parent_id'];

    // Scope helper
    public function scopeCapaian($q) { return $q->where('type','capaian'); }
    public function scopeKpmr   ($q) { return $q->where('type','kpmr'); }

    public function parent()
    {
        return $this->belongsTo(ParameterKinerja::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ParameterKinerja::class, 'parent_id');
    }

    public function options()
    {
        return $this->hasMany(PilihanParameterKinerja::class, 'parameter_id');
    }
}
