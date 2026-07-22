<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class LevelSeeder extends Seeder
{
    use DatabaseSeederTrait; // Tambahkan baris ini
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->disableForeignKeyChecks();
        DB::table('levels')->truncate();
        $this->enableForeignKeyChecks();

        // Waktu saat ini
        $now = Carbon::now();

        $levels = [
            ['name' => 'Risk Officer', 'code' => null, 'description' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['name' => 'Risk Owner', 'code' => null, 'description' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['name' => 'PjFs Finance Risk Management', 'code' => null, 'description' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
            ['name' => 'PjFK Risk Management', 'code' => null, 'description' => null, 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => null],
        ];

        DB::table('levels')->insert($levels);
    }
}
