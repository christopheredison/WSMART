<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectSektor extends Model
{
    //ini adalah konstruksi spesifik
    use HasFactory;

    protected $fillable = [
        'sektor_name',
        'project_divisi_id',
    ];

    public function projectDivisi()
    {
        return $this->belongsTo(ProjectDivisi::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
