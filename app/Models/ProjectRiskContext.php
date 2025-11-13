<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectRiskContext extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'project_risk_contexts';

    protected $fillable = [
        'project_id',
        'nilai',
        'pimpinan_tertinggi_jabatan_id',
        'sponsor',
        'deskripsi',
        'tujuan',
        'lingkup_pekerjaan',
        'pekerjaan_luar_lingkup',
        'sasaran',
        'batasan',
        'asumsi_dasar',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function pimpinanTertinggi()
    {
        return $this->belongsTo(Jabatan::class, 'pimpinan_tertinggi_jabatan_id');
    }

    // RELASI HASMANY BARU
    public function members()
    {
        return $this->hasMany(ProjectRiskContextMember::class, 'project_risk_context_id');
    }

    public function stakeholderInternals()
    {
        return $this->hasMany(ProjectRiskContextStakeholderInternal::class, 'project_risk_context_id');
    }

    public function stakeholderExternals()
    {
        return $this->hasMany(ProjectRiskContextStakeholderExternal::class, 'project_risk_context_id');
    }
}
