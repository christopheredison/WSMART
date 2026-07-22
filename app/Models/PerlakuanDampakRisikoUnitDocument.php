<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PerlakuanDampakRisikoUnitDocument extends Model
{
    protected $fillable = [
        'perlakuan_dampak_risiko_unit_id',
        'unit_risk_monitoring_id',
        'user_id',
        'file_name',
        'file_path',
        'mimetype',
        'description',
    ];

    protected $casts = [
        'file_name' => 'string',
        'file_path' => 'string',
        'mimetype' => 'string',
        'description' => 'string',
    ];

    public $appends = [
        'url',
    ];

    public function perlakuanDampakRisikoUnit()
    {
        return $this->belongsTo(PerlakuanDampakRisikoUnit::class);
    }

    public function unitRiskMonitoring()
    {
        return $this->belongsTo(UnitRiskMonitoring::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getUrlAttribute() {
        return Storage::url($this->file_path);
    }

    public function delete()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            Storage::disk('public')->delete($this->file_path);
        }
        return parent::delete();
    }
}
