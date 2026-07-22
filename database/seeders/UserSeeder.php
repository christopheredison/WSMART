<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Supports\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $userAdmin = User::create([
            'unit_type_id' => '1',
            'unit_id' => '1',
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'type' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = Role::findById(7);
        $userAdmin->assignRole($role);
    }
}
