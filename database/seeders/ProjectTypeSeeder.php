<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Traits\DatabaseSeederTrait;

class ProjectTypeSeeder extends Seeder
{
    use DatabaseSeederTrait;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->disableForeignKeyChecks();
        DB::table('project_types')->truncate();
        $this->enableForeignKeyChecks();

        $now = Carbon::now();

        $datas = [
            ['id' => 1, 'name' => 'Airport', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 2, 'name' => 'Building', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 3, 'name' => 'Bored Pile', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 4, 'name' => 'Dam', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 5, 'name' => 'Industrial', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 6, 'name' => 'Investment Project', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 7, 'name' => 'Irrigation', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 8, 'name' => 'Mining', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 9, 'name' => 'Oil & Gas', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 10, 'name' => 'Port', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 11, 'name' => 'Power Plant', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 12, 'name' => 'Precast', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 13, 'name' => 'Rail Way', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 14, 'name' => 'Road and Bridge', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 15, 'name' => 'Research & Development', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 16, 'name' => 'Ready Mix', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 17, 'name' => 'Rental', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 18, 'name' => 'Sport Facilities', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 19, 'name' => 'Transmission Line', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
            ['id' => 20, 'name' => 'Tunnet', 'created_at' => $now, 'updated_at' => $now, 'deleted_at' => NULL],
        ];

        DB::table('project_types')->insert($datas);
    }
}
