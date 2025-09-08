<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkalaParameter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type_parameter',
        'tingkat',
        'skala',
        'deskripsi',
        'min',
        'max',
    ];
}