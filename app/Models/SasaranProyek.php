<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SasaranProyek extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'costcenter_code',
        'kpi_desc',
        'status',
        'tahun',
        'kpi_id',
        'target_akhir_tahun',
        'satuan'
    ];

    //data sasaran default tapi hanya informasi keterangan saja
    public static function getDefaults()
    {
        $defaults = [
            'default_jadwal_pelaksanaan' => 'Kepatuhan terhadap Jadwal Pelaksanaan',
            'default_anggaran_biaya' => 'Kepatuhan terhadap Anggaran Biaya',
            'default_mutu_spesifikasi' => 'Mutu dan Spesifikasi Terpenuhi',
            'default_kinerja_k3' => 'Kinerja K3 (Keselamatan dan Kesehatan Kerja)',
            'default_regulasi_perizinan' => 'Kepatuhan terhadap Regulasi dan Perizinan',
            'default_kepuasan_stakeholder' => 'Kepuasan Stakeholder & Pemberi Kerja',
            'default_ketersediaan_sumber_daya' => 'Ketersediaan Sumber Daya Proyek',
            'default_koordinasi_komunikasi' => 'Koordinasi dan Komunikasi Tim',
            'default_efektivitas_pengendalian' => 'Efektivitas Pengendalian Perubahan (Change Order)',
            'default_keamanan_fisik' => 'Keamanan Fisik Proyek dan Aset',
            'default_pengelolaan_subkontraktor' => 'Pengelolaan Subkontraktor dan Vendor',
            'default_dokumentasi_pelaporan' => 'Dokumentasi dan Pelaporan Proyek',
            'default_kepatuhan_target_tkdn' => 'Kepatuhan terhadap Target TKDN atau Komitmen Sosial',
            'default_efisiensi_operasional' => 'Efisiensi Operasional dan Produktivitas',
            'default_kesiapan_serah_terima' => 'Kesiapan Serah Terima Proyek (PHO/FHO)',
        ];

        return collect($defaults)->map(function ($desc, $id) {
            $sasaran = new SasaranProyek();
            // Penting: Set incrementing ke false dan keyType ke string agar ID tidak dicast ke integer (0)
            $sasaran->incrementing = false;
            $sasaran->keyType = 'string';
            
            $sasaran->forceFill([
                'id' => $id,
                'kpi_desc' => $desc,
            ]);
            return $sasaran;
        })->values();
    }
}
