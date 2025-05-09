<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkalaProbabilitas extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $table = 'skala_probabilitas';

    protected $fillable = [
        'min',
        'max',
        'type_risiko',
        'tingkat',
        'skala',
        'deskripsi',
    ];

    public const TYPE_RISIKO_UMUM = 'Umum';
    public const TYPE_RISIKO_MEDIS = 'Medis';

    public function scopeUmum($query)
    {
        return $query->where('type_risiko', static::TYPE_RISIKO_UMUM);
    }

    public static function getSkalaByValue($value, $type = null)
    {
        if (!$type) {
            $type = static::TYPE_RISIKO_UMUM;
        }

        return static::where('type_risiko', $type)
            ->where('min', '<=', $value)
            ->orderBy('min', 'desc')
            ->first();
    }
}
