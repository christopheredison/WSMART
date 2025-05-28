<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;
use App\Models\Tck;

class SasaranSeeder extends Seeder
{
    use DatabaseSeederTrait;
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('tcks')->truncate();
        $this->enableForeignKeyChecks();

        // Data sasaran yang akan dimasukkan
        $sasaran = [
            ['id' => 1, 'periode_id' => 1, 'unit_id' => 1, 'title' => 'Sasaran 1', 'created_at' => '2025-05-25 11:46:28', 'updated_at' => '2025-05-25 11:46:28', 'deleted_at' => null],
            ['id' => 2, 'periode_id' => 1, 'unit_id' => 1, 'title' => 'Sasaran 2', 'created_at' => '2025-05-25 11:46:28', 'updated_at' => '2025-05-25 11:46:28', 'deleted_at' => null],
        ];

        // Masukkan data ke dalam tabel tcks
        DB::table('tcks')->insert($sasaran);
    }
}