<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventChild extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'loss_event_children';

    public function lossEvent()
    {
        return $this->belongsTo(LossEvent::class, 'loss_event_id');
    }
}
