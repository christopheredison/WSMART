<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriKejadian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['kategori_kejadian', 'type'];
}
