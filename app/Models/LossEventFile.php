<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventFile extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'loss_event_files';

    public function lostEvent()
    {
        return $this->belongsTo(LossEvent::class, 'lost_event_id');
    }
}
