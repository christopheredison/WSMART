<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectDivisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'divisi_name',
    ];

    public function projectSektors()
    {
        return $this->hasMany(ProjectSektor::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
