<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LossEventProject extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $fillable = [
        'tahun',
        'deskripsi_kejadian',
        'jumlah_kejadian',
        'peristiwa_risiko_id',
        'project_sektor_id',
        'project_id',
        'tanggal_kejadian',
        'rentang_kejadian_awal',
        'rentang_kejadian_akhir',
        'nilai_kerugian_finansial',
        'nilai_kerugian_non_finansial',
        'unit_penanggung_jawab',
        'nama_kejadian',
        'kategori_kejadian_id',
        'sumber_penyebab_kejadian',
        'penyebab_masalah',
        'penanganan_kejadian',
        'kategori_risiko_bumn',
        'kategori_risiko_id',
        'jenis_risiko_id',
        'penjelasan_kerugian',
        'kejadian_berulang',
        'frekuensi_kejadian',
        'rencana_mitigasi',
        'realisasi_mitigasi',
        'perbaikan_mendatang',
        'status_asuransi',
        'nilai_premi',
        'nilai_klaim',
        'status_risk_register',
        'no_urut_risiko',
        'biaya_risiko_inheren',
        'biaya_upaya_perbaikan',
        'hasil_perbaikan',
    ];

    public function projectSektor()
    {
        return $this->belongsTo(ProjectSektor::class, 'project_sektor_id');
    }

    public function peristiwaRisiko()
    {
        return $this->belongsTo(PeristiwaRisiko::class, 'peristiwa_risiko_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    // Relasi dengan KategoriKejadian
    public function kategoriKejadian()
    {
        return $this->belongsTo(KategoriKejadian::class);
    }

    // Relasi dengan KategoriRisiko
    public function kategoriRisiko()
    {
        return $this->belongsTo(KategoriRisiko::class);
    }

    // Relasi dengan JenisRisiko
    public function jenisRisiko()
    {
        return $this->belongsTo(JenisRisiko::class);
    }
}
