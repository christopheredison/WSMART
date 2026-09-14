<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use Illuminate\Database\Seeder;

class GlobalSettingSeeder extends Seeder
{
    public function run(): void
    {
        GlobalSetting::updateOrCreate(
            ['name' => 'project_status_threshold_days'],
            ['value' => '45']
        );
    }
}
