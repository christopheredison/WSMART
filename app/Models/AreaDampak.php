<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AreaDampak extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'area_dampaks';
    protected $fillable = [
        'title',
        'type',
        'deskrpsi',
    ];

    public const TYPE_UMUM = 'Umum';
    public const TYPE_Project = 'Project';

    public function scopeUmum(Builder $query): void {
        $query->where('type', static::TYPE_UMUM);
    }

    public function scopeProject(Builder $query): void {
        $query->where('type', static::TYPE_Project);
    }

    public function details()
    {
        return $this->hasMany(AreaDampakDetail::class, 'area_dampak_id');
    }
}
