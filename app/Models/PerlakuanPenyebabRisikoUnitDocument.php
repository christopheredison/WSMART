<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PerlakuanPenyebabRisikoUnitDocument extends Model
{
    protected $fillable = [
        'perlakuan_penyebab_risiko_unit_id',
        'unit_risk_monitoring_id',
        'user_id',
        'quarter',
        'file_name',
        'file_path',
        'mimetype',
        'description',
    ];

    public $appends = [
        'url',
    ];

    public function perlakuanPenyebabRisikoUnit()
    {
        return $this->belongsTo(PerlakuanPenyebabRisikoUnit::class, 'perlakuan_penyebab_risiko_unit_id', 'id');
    }

    public function unitRiskMonitoring()
    {
        return $this->belongsTo(UnitRiskMonitoring::class, 'unit_risk_monitoring_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
