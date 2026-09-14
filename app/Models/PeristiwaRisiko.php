<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PeristiwaRisiko extends Model
{
    use HasFactory, SoftDeletes;

    public const APPROVAL_PENDING = 0;
    public const APPROVAL_APPROVED = 1;
    public const APPROVAL_REJECTED = 2;

    public const STATUS_MASTER = 1;
    public const STATUS_CUSTOM = 2;

    protected $guarded = [];
    protected $table = 'peristiwa_risikos';

    protected $fillable = [
        'kategori_risiko_id',
        'jenis_risiko_id',
        'title',
        'deskripsi',
        'unit_type_id',
        'type',
        'project_id',
        'project_periode_list_id',
        'requested_by',
        'status',
        'approval_status',
        'verified_by',
        'verified_at',
        'rejected_reason',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function scopeUsableForProject($query, $includeId = null)
    {
        return $query->where(function ($q) use ($includeId) {
            $q->where('type', 2)
                ->where(function ($q2) {
                    $q2->whereNull('approval_status')
                        ->orWhere('approval_status', self::APPROVAL_APPROVED);
                });

            if ($includeId) {
                $q->orWhere('id', $includeId);
            }
        });
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function rencanaPerlakuanRisiko()
    {
        return $this->hasOne(RencanaPerlakuanRisiko::class, 'risiko_id');
    }

    public function penyebabRisiko()
    {
        return $this->hasOne(PenyebabRisiko::class, 'risiko_id');
    }

    public function monitoringRisiko()
    {
        return $this->hasMany(MonitoringRisiko::class, 'risiko_id');
    }

    public function kri() {
        return $this->hasOne(KRI::class, 'risiko_id');
    }

    public function kontrolEksistings()
    {
        return $this->hasMany(KontrolEksisting::class, 'peristiwa_risiko_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function projectPeriodeList()
    {
        return $this->belongsTo(ProjectPeriodeList::class, 'project_periode_list_id');
    }
}
