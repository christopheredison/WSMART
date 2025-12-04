<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'risiko_id',
        'type',
        'status',
        'notes',
        'user_id',
    ];

    // public function risiko()
    // {
    //     return $this->belongsTo(IdentifikasiRisiko::class, 'risiko_id');
    // }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
