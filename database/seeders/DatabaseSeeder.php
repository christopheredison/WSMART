<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            MasterSeeder::class,
            KategoriRisikoSeeder::class,
            JenisRisikoSeeder::class,
            PeristiwaRisikoSeeder::class,
            //KRIProjectSeeder::class,
            //KontrolEksistingSeeder::class,
            JenisKontrolEksistingSeeder::class,
            JenisProgramRKAPSeeder::class,
            //KonstruksiSpesifikSeeder::class,
            OpsiPerlakuanRisikoSeeder::class,
            //ProjectTypeSeeder::class,
            RiskDataSeeder::class,
            AreaDampakSeeder::class,
            AreaDampakDetailSeeder::class,
            FinalRatingSeeder::class,
            JenisRencanaPerlakuanRisikoSeeder::class,
            ParameterKinerjaSeeder::class,
            PenilaianEfektivitasKontrolSeeder::class,
            SkalaKinerjaSeeder::class,
            SkalaKPMRSeeder::class,
            SkalaParameterSeeder::class,
        ]);
    }
}
