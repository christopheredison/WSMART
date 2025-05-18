<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkalaKinerja extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'skala_kinerjas';

    protected $fillable = [
        'id',
        'tingkat',
        'deskripsi',
        'min',
        'max',
    ];
}
