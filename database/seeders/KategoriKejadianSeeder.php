<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriKejadian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KategoriKejadianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Soft delete semua data yang ada
        KategoriKejadian::query()->update(['deleted_at' => Carbon::now()]);

        // Data baru untuk dimasukkan
        $kategoriKejadians = [
            'Perampokan Bersenjata',
            'Kecelakaan',
            'Tabrakan',
            'Kejahatan Siber',
            'Gempa Bumi',
            'Pengaturan / Penanganan yang salah',
            'Kecurangan (Penipuan)',
            'Kebakaran',
            'Banjir',
            'Penyakit',
            'Petir',
            'Kelalaian',
            'Kerusuhan / Perang',
            'Korsleting / Arus Pendek',
            'Mogok Kerja',
            'Pencurian',
            'Angin Topan / Badai',
            'Lainnya',
        ];

        // Masukkan data baru
        foreach ($kategoriKejadians as $kategori) {
            KategoriKejadian::create([
                'kategori_kejadian' => $kategori
            ]);
        }
    }
}
