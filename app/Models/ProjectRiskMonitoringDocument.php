<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ProjectRiskMonitoringDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_risk_id',
        'user_id',
        'quarter',
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

    public function projectRisk()
    {
        return $this->belongsTo(ProjectRisk::class);
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
