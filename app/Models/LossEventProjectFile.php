<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventProjectFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_event_project_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size'
    ];

    public function lossEventProject()
    {
        return $this->belongsTo(LossEventProject::class, 'loss_event_project_id');
    }
}