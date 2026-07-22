<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['location'];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
