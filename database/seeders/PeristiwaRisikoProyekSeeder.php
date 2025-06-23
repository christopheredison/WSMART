<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PeristiwaRisiko;

class PeristiwaRisikoProyekSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Hapus data peristiwa risiko dengan type=2 (Proyek)
        PeristiwaRisiko::where('type', 2)->delete();

        // Daftar peristiwa risiko proyek
        $peristiwaRisikoProyek = [
            'Bencana Alam',
            'Cashflow Negatif',
            'Denda keterlambatan / Performance / Pemenuhan Regulasi',
            'Desain Berubah / Tidak Lengkap / Tidak Aman',
            'Dispute Kontraktual',
            'Harga satuan kontrak rendah / Timpang',
            'Jalan akses tidak memadai',
            'Kebakaran di area proyek',
            'Kecelakaan Kerja',
            'Kehilangan material / barang / equipment',
        ];

        // Tambahkan data peristiwa risiko proyek
        foreach ($peristiwaRisikoProyek as $title) {
            PeristiwaRisiko::create([
                'title' => $title,
                'type' => 2,
                'kategori_risiko_id' => null,
                'jenis_risiko_id' => null,
            ]);
        }
    }
}
