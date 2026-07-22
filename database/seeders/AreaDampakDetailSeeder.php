<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\Traits\DatabaseSeederTrait;
class AreaDampakDetailSeeder extends Seeder
{
    use DatabaseSeederTrait;
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->disableForeignKeyChecks();
        DB::table('area_dampak_details')->truncate();
        $this->enableForeignKeyChecks();

        $path = database_path('seeders/sql/area_dampak_detail.sql');
        $sql  = File::get($path);

        DB::unprepared($sql);
    }
}
