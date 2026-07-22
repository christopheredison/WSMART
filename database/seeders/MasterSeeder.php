<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AreaDampak;
use App\Models\KategoriRisiko;
use App\Models\JenisRisiko;
use App\Models\Periode;
use App\Models\PeristiwaRisiko;
use App\Models\RencanaKegiatan;
use App\Models\SikapRisiko;
use App\Models\SkalaDampak;
use App\Models\SkalaProbabilitas;
use App\Models\RiskSetting;
use App\Models\RiskSettingChild;
use App\Models\RiskMap;
use App\Models\Tck;
use App\Models\Unit;
use App\Models\UnitType;


class MasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Periode::create([
            'tahun' => '2024',
            'status' => 'active'
        ]);

        SikapRisiko::create([
            'jenis_sikap' => 'Konservatif'
        ]);

        SikapRisiko::create([
            'jenis_sikap' => 'Moderat'
        ]);

        SikapRisiko::create([
            'jenis_sikap' => 'Agresif'
        ]);

        SkalaDampak::create([
            'tingkat' => '1',
            'deskripsi' => 'Sangat Rendah'
        ]);

        SkalaDampak::create([
            'tingkat' => '2',
            'deskripsi' => 'Rendah'
        ]);

        SkalaDampak::create([
            'tingkat' => '3',
            'deskripsi' => 'Moderat'
        ]);

        SkalaDampak::create([
            'tingkat' => '4',
            'deskripsi' => 'Tinggi'
        ]);

        SkalaDampak::create([
            'tingkat' => '5',
            'deskripsi' => 'Sangat Tinggi'
        ]);

        SkalaProbabilitas::create([
            'min' => '0',
            'max' => '20',
            'type_risiko' => 'Umum',
            'tingkat' => '1',
            'skala' => 'Sangat jarang terjadi',
            'deskripsi' => 'Probabilitas kejadian Risiko di bawah 20%'
        ]);

        SkalaProbabilitas::create([
            'min' => '20',
            'max' => '40',
            'type_risiko' => 'Umum',
            'tingkat' => '2',
            'skala' => 'Jarang terjadi',
            'deskripsi' => 'Probabilitas kejadian Risiko dari 20% sampai dengan 40%'
        ]);

        SkalaProbabilitas::create([
            'min' => '40',
            'max' => '60',
            'type_risiko' => 'Umum',
            'tingkat' => '3',
            'skala' => 'Bisa terjadi',
            'deskripsi' => 'Probabilitas kejadian Risiko antara 40% sampai dengan 60%'
        ]);

        SkalaProbabilitas::create([
            'min' => '60',
            'max' => '80',
            'type_risiko' => 'Umum',
            'tingkat' => '4',
            'skala' => 'Sangat mungkin terjadi',
            'deskripsi' => 'Probabilitas kejadian Risiko antara 60% sampai dengan 80%'
        ]);

        SkalaProbabilitas::create([
            'min' => '80',
            'max' => '100',
            'type_risiko' => 'Umum',
            'tingkat' => '5',
            'skala' => 'Hampir pasti terjadi',
            'deskripsi' => 'Probabilitas kejadian Risiko antara 80% sampai dengan 99%'
        ]);

        SkalaProbabilitas::create([
            'min' => '0',
            'max' => '10',
            'type_risiko' => 'Medis',
            'tingkat' => '1',
            'skala' => 'Hampir tidak terjadi',
            'deskripsi' => 'Peristiwa hanya akan timbul pada kondisi yang luar biasa'
        ]);

        SkalaProbabilitas::create([
            'min' => '10',
            'max' => '30',
            'type_risiko' => 'Medis',
            'tingkat' => '2',
            'skala' => 'Jarang terjadi',
            'deskripsi' => 'Peristiwa diharapkan tidak terjadi'
        ]);

        SkalaProbabilitas::create([
            'min' => '30',
            'max' => '50',
            'type_risiko' => 'Medis',
            'tingkat' => '3',
            'skala' => 'Kadang terjadi',
            'deskripsi' => 'Peristiwa kadang-kadang bisa terjadi'
        ]);

        SkalaProbabilitas::create([
            'min' => '50',
            'max' => '90',
            'type_risiko' => 'Medis',
            'tingkat' => '4',
            'skala' => 'Sering terjadi',
            'deskripsi' => 'Peristiwa sangat mungkin terjadi pada sebagian kondisi'
        ]);

        SkalaProbabilitas::create([
            'min' => '90',
            'max' => '100',
            'type_risiko' => 'Medis',
            'tingkat' => '5',
            'skala' => 'Hampir Pasti terjadi',
            'deskripsi' => 'Peristiwa selalu terjadi hampir pada setiap kondisi'
        ]);

        KategoriRisiko::create([
            'title' => 'Kategori Risiko Fiskal',
            'unit_type_id' => 4
        ]);

        KategoriRisiko::create([
            'title' => 'Kategori Risiko Industri Umum',
            'unit_type_id' => 4
        ]);

        JenisRisiko::create([
            'title' => 'Peristiwa Risiko terkait Kebijakan Sektoral',
            'kategori_risiko_id' => 1,
            'unit_type_id' => 4
        ]);

        JenisRisiko::create([
            'title' => 'Peristiwa Risiko terkait Pasar dan Makro Ekonomi',
            'kategori_risiko_id' => 2,
            'unit_type_id' => 4
        ]);

        JenisRisiko::create([
            'title' => 'Peristiwa Risiko terkait Keuangan',
            'kategori_risiko_id' => 2,
            'unit_type_id' => 4
        ]);

        PeristiwaRisiko::create([
            'kategori_risiko_id' => '1',
            'jenis_risiko_id' => '1',
            'title' => 'Politik',
            'unit_type_id' => 4
        ]);

        PeristiwaRisiko::create([
            'kategori_risiko_id' => '2',
            'jenis_risiko_id' => '2',
            'title' => 'Kenaikan Harga',
            'unit_type_id' => 4
        ]);

        PeristiwaRisiko::create([
            'kategori_risiko_id' => '2',
            'jenis_risiko_id' => '2',
            'title' => 'Nilai Tukar Mata Uang',
            'unit_type_id' => 4
        ]);

        PeristiwaRisiko::create([
            'kategori_risiko_id' => '2',
            'jenis_risiko_id' => '2',
            'title' => 'Perubahan Suku Bunga',
            'unit_type_id' => 4
        ]);
        
        PeristiwaRisiko::create([
            'kategori_risiko_id' => '2',
            'jenis_risiko_id' => '3',
            'title' => 'Pembayaran/Pendanaan',
            'unit_type_id' => 4
        ]);

        PeristiwaRisiko::create([
            'kategori_risiko_id' => '2',
            'jenis_risiko_id' => '2',
            'title' => 'Perpajakan',
            'unit_type_id' => 4
        ]);

        AreaDampak::create([
            'title' => 'Area Dampak 1',
            'type' => 'Umum',
        ]);

        RiskSetting::create([
            'efektivitas_control' => 'Cukup dan Efektif'
        ]);

        RiskSetting::create([
            'efektivitas_control' => 'Cukup dan Efektif Sebagian'
        ]);

        RiskSetting::create([
            'efektivitas_control' => 'Cukup dan Tidak Efektif'
        ]);

        RiskSetting::create([
            'efektivitas_control' => 'Tidak Cukup dan Tidak Efektif'
        ]);

        RiskSetting::create([
            'efektivitas_control' => 'Tidak Cukup dan Sangat Tidak Efektif'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Dokumen sistem manajemen telah dirumuskan secara tertulis dan memadai',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Otomasi'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Diawasi berjenjang seluruhnya (6 eyes principle)'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Lulus audit dan tidak ada temuan'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Diperbarui berkala dan sesuai perkembangan'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '1',
            'keterangan' => 'Kontrol bersifat preventif dan dapat mendeteksi risiko >3 bulan sebelum terjadi'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Dokumen sistem manajemen telah dirumuskan secara tertulis dan memadai'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Otomasi'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Diawasi berjenjang sebagian besar (4 eyes principle)',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Diaudit namun masih ada sedikit temuan',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Diperbarui berkala namun belum sesuai perkembangan',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '2',
            'keterangan' => 'Kontrol bersifat preventif dan dapat mendeteksi risiko 2-3 bulan sebelum terjadi',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Dokumen sistem manajemen terkait telah dirumuskan secara tertulis namun belum memadai (ada proses yang belum memiliki pedoman tertulis)',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Semi Otomasi',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Diawasi berjenjang sebagian besar (4 eyes principle)',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Diaudit namun masih ada beberapa temuan',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Diperbarui namun belum berkala dan belum sesuai perkembangan',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '3',
            'keterangan' => 'Kontrol bersifat preventif dan dapat mendeteksi risiko 1-2 bulan sebelum terjadi',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Dokumen sistem manajemen terkait telah dirumuskan secara tertulis namun belum memadai (ada proses yang belum memiliki pedoman tertulis)',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Manual'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Pengawasan berjenjang sangat terbatas',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Belum pernah diaudit',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Belum pernah diperbarui',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '4',
            'keterangan' => 'Kontrol bersifat korektif dan dapat mendeteksi risiko setelah risiko terjadi',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Dokumen sistem manajemen terkait belum dirumuskan secara tertulis',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Manual'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Tidak ada pengawasan berjenjang',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Belum pernah diaudit',
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Kontrol bersifat korektif dan dapat mendeteksi risiko beberapa hari setelah risiko terjadi'
        ]);

        RiskSettingChild::create([
            'risk_setting_id' => '5',
            'keterangan' => 'Kontrol bersifat preventif dan dapat mendeteksi risiko >3 bulan sebelum terjadi'
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '7',
            'skala_dampak' => 1,
            'skala_probabilitas' => 5
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate',
            'nilai_risiko' => '12',
            'skala_dampak' => 2,
            'skala_probabilitas' => 5
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate to High',
            'nilai_risiko' => '17',
            'skala_dampak' => 3,
            'skala_probabilitas' => 5
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '22',
            'skala_dampak' => 4,
            'skala_probabilitas' => 5
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '25',
            'skala_dampak' => 5,
            'skala_probabilitas' => 5
        ]);

        RiskMap::create([
            'level_risiko' => 'Low',
            'nilai_risiko' => '4',
            'skala_dampak' => 1,
            'skala_probabilitas' => 4
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '9',
            'skala_dampak' => 2,
            'skala_probabilitas' => 4
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate',
            'nilai_risiko' => '14',
            'skala_dampak' => 3,
            'skala_probabilitas' => 4
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate to High',
            'nilai_risiko' => '19',
            'skala_dampak' => 4,
            'skala_probabilitas' => 4
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '24',
            'skala_dampak' => 5,
            'skala_probabilitas' => 4
        ]);

        RiskMap::create([
            'level_risiko' => 'Low',
            'nilai_risiko' => '3',
            'skala_dampak' => 1,
            'skala_probabilitas' => 3
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '8',
            'skala_dampak' => 2,
            'skala_probabilitas' => 3
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate',
            'nilai_risiko' => '13',
            'skala_dampak' => 3,
            'skala_probabilitas' => 3
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate to High',
            'nilai_risiko' => '18',
            'skala_dampak' => 4,
            'skala_probabilitas' => 3
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '23',
            'skala_dampak' => 5,
            'skala_probabilitas' => 3
        ]);

        RiskMap::create([
            'level_risiko' => 'Low',
            'nilai_risiko' => '2',
            'skala_dampak' => 1,
            'skala_probabilitas' => 2
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '6',
            'skala_dampak' => 2,
            'skala_probabilitas' => 2
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '11',
            'skala_dampak' => 3,
            'skala_probabilitas' => 2
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate to High',
            'nilai_risiko' => '16',
            'skala_dampak' => 4,
            'skala_probabilitas' => 2
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '21',
            'skala_dampak' => 5,
            'skala_probabilitas' => 2
        ]);

        RiskMap::create([
            'level_risiko' => 'Low',
            'nilai_risiko' => '1',
            'skala_dampak' => 1,
            'skala_probabilitas' => 1
        ]);

        RiskMap::create([
            'level_risiko' => 'Low',
            'nilai_risiko' => '5',
            'skala_dampak' => 2,
            'skala_probabilitas' => 1
        ]);

        RiskMap::create([
            'level_risiko' => 'Low to Moderate',
            'nilai_risiko' => '10',
            'skala_dampak' => 3,
            'skala_probabilitas' => 1
        ]);

        RiskMap::create([
            'level_risiko' => 'Moderate',
            'nilai_risiko' => '15',
            'skala_dampak' => 4,
            'skala_probabilitas' => 1
        ]);

        RiskMap::create([
            'level_risiko' => 'High',
            'nilai_risiko' => '20',
            'skala_dampak' => 5,
            'skala_probabilitas' => 1
        ]);

        //Master Unit Type
        UnitType::create([
            'name' => 'Corporate'
        ]);

        UnitType::create([
            'name' => 'Unit Bisnis'
        ]);

        UnitType::create([
            'name' => 'Anak Perusahaan'
        ]);

        //Master Unit
        Unit::create([
            'unit_type_id' => '1',
            'name' => 'Corporate',
            'parent_id' => 0,
        ]);

        //Proyek
        Unit::create([
            'unit_type_id' => '2',
            'name' => 'Unit A',
            'parent_id' => 0,
        ]);
    }
}
