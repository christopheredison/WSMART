<?php

namespace App\Models;

use App\Traits\HasRiskNoteStatuses;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskNote extends Model
{
    use HasFactory, HasRiskNoteStatuses, SoftDeletes;

    protected $fillable = [
        'risiko_id',
        'type',
        'status',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
