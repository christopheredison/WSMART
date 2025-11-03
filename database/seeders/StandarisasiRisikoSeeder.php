<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeristiwaRisiko;

class StandarisasiRisikoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data peristiwa risiko dengan type=1 (Standarisasi)
        PeristiwaRisiko::where('type', 1)->delete();

        // Daftar peristiwa risiko standarisasi
        $peristiwaRisikoStandarisasi = [
            'Kenaikan harga-harga kebutuhan proyek',
            'Selisih Kurs',
            'Koreksi Restitusi Pajak',
            'Klaim dari pihak ketiga / owner',
            'Klaim ke owner / Pihak Ketiga ditolak / Turun',
            'Temuan Audit Eksternal',
            'Inkompatibilitas model BIM dengan data lapangan',
            'Keterlambatan proses commissioning',
            'Kerusakan Bangunan/Utilitas eksisting',
            'Keterlambatan Pekerjaan',
            'Kualitas Pekerjaan/Produk rendah',
            'Pekerjaan Kurang',
            'Pekerjaan tambah tidak diakui',
            'Penurunan penjualan',
            'Percepatan Penyelesaian Pekerjaan',
            'Perpanjangan Waktu Pelaksanaan',
            'Perubahan Metode Kerja',
            'Proyek suspend / slowdown',
            'Serah terima lahan/Pekerjaan yang terlambat',
            'Unforeseen condition',
            'Volume pekerjaan bertambah',
            'Waste material tinggi',
            'Produktivitas yang rendah',
            'Lingkup Pekerjaan berubah/tidak jelas',
            'Kegagalan integrasi sistem manajemen proyek',
            'Gangguan fungsi fasilitas',
            'Ketidaksesuaian hasil pengujian',
            'Serangan Cyber',
            'Kondisi Lingkungan Tidak Aman',
            'Pencemaran Lingkungan',
            'Wabah Pandemi',
            'Konflik sosial',
            'Keterbatasan sumber daya',
            'Pengadaan sumber daya terlambat',
            'Vendor tidak perform',
            'Kompetensi SDM yang tidak memenuhi syarat',
            'Member KSO tidak perform',
        ];

        // Tambahkan data peristiwa risiko standarisasi
        foreach ($peristiwaRisikoStandarisasi as $title) {
            PeristiwaRisiko::create([
                'title' => $title,
                'type' => 2,
                'kategori_risiko_id' => null,
                'jenis_risiko_id' => null,
            ]);
        }
    }
}