<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossEventApFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'loss_event_ap_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size'
    ];

    public function lossEventAp()
    {
        return $this->belongsTo(LossEventAp::class, 'loss_event_ap_id');
    }
}