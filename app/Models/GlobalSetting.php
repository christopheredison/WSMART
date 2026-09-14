<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GlobalSetting extends Model
{
    use SoftDeletes;

    protected $table = 'global_settings';

    protected $fillable = ['name', 'value'];

    public static function getValue(string $name, $default = null): ?string
    {
        return static::where('name', $name)->value('value') ?? $default;
    }
}
