<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PerlakuanPenyebabRisikoDocument extends Model
{
    protected $fillable = [
        'perlakuan_penyebab_risiko_id',
        'project_monitoring_id',
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

    public function perlakuanPenyebabRisiko()
    {
        return $this->belongsTo(PerlakuanPenyebabRisiko::class);
    }

    public function projectMonitoring()
    {
        return $this->belongsTo(ProjectRiskMonitoring::class);
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
        Storage::delete($this->file_path);
        return parent::delete();
    }
}
