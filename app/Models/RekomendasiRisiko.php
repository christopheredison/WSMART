<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RekomendasiRisiko extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rekomendasi_risikos';

    protected $fillable = [
        'unit_type_id',
        'unit_id',
        'periode_id',
        'user_id',
        'kategori_risiko_id',
        'jenis_risiko_id',
        'peristiwa_risiko_id',
        'target_capaian_kinerja',
        'rencana_kegiatan',
        'peristiwa_risiko',
        'deskripsi_peristiwa_risiko',
        'wbs',
        'deskripsi_rencana_kegiatan',
        'type',
        'jenis_kontrol_eksisting_id',
        'kontrol_eksisting',
        'penilaian_efektifitas_kontrol',
        'perkiraan_waktu_terpapar_risiko_mulai',
        'perkiraan_waktu_terpapar_risiko_akhir',
        'status_risiko',
        'status_progress',
        'type_risiko',
        'catatan',
        'skala_risiko',
        'level_risiko',
        'status',
        'step_verification',
        'is_corporate',
        'previous_status_risiko',
        'is_closed',
        'efektivitas_perlakuan_risiko',
    ];

    // Status Constants
    public const STATUS_DRAFT = 1;
    public const STATUS_PUBLISHED = 2;


    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class, 'kategori_risiko_id');
    }

    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class, 'jenis_risiko_id');
    }

    public function jenisKontrolEksisting()
    {
        return $this->belongsTo(JenisKontrolEksisting::class, 'jenis_kontrol_eksisting_id');
    }

    public function penyebabRisikos()
    {
        return $this->hasMany(RekomendasiPenyebabRisiko::class, 'rekomendasi_risiko_id');
    }

    public function kris()
    {
        return $this->hasMany(RekomendasiKRI::class, 'rekomendasi_risiko_id');
    }

    public function kontrolEksistings()
    {
        return $this->hasMany(RekomendasiKontrolEksisting::class, 'rekomendasi_risiko_id');
    }

    public function penilaianEfektifitasKontrol()
    {
        return $this->belongsTo(PenilaianEfektivitasKontrol::class, 'penilaian_efektifitas_kontrol');
    }
}
