<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventFile extends Model
{
    use HasFactory;

    protected $table = 'loss_event_files';
    protected $fillable = [
        'loss_event_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size'
    ];

    public function lostEvent()
    {
        return $this->belongsTo(LossEvent::class, 'lost_event_id');
    }
}
