<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataBatchNotes extends Model
{
    use HasFactory;

    protected $fillable = [
        'data_batch_id',
        'notes',
        'step_order',
        'unread',
        'user_id',
    ];

    /**
     * Get the data batch that owns the note.
     */
    public function dataBatch()
    {
        return $this->belongsTo(DataBatch::class, 'data_batch_id');
    }

    /**
     * Get the user that created the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
