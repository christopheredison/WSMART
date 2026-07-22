<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UnitType;
use App\Models\Unit;
use App\Models\Tck;
use App\Supports\Helper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inputRole = Role::create([
            'name' => 'input'
        ]);

        $validasiRole = Role::create([
            'name' => 'validasi'
        ]);

        $verifikatorRole = Role::create([
            'name' => 'verifikator'
        ]);

        $viewRole = Role::create([
            'name' => 'view'
        ]);

        $viewChildRole  = Role::create([
            'name' => 'view_child'
        ]);

        $manajemen_risikoRole = Role::create([
            'name' => 'manajemen_risiko'
        ]);

        $adminRole = Role::create([
            'name' => 'admin'
        ]);

        $tkmruRole = Role::create([
            'name' => 'capaian_tkmru'
        ]);

        $tckRole = Role::create([
            'name' => 'capaian_tck'
        ]);

        $riskStrategyRole = Role::create([
            'name' => 'strategi_risiko'
        ]);

        //Master Permission

        $riskRegisterListPermission = Permission::create([
            'name' => 'risk_register_list'
        ]);

        $riskRegisterViewPermission = Permission::create([
            'name' => 'risk_register_view'
        ]);

        $riskRegisterCreatePermission = Permission::create([
            'name' => 'risk_register_create'
        ]);

        $riskRegisterEditPermission = Permission::create([
            'name' => 'risk_register_edit'
        ]);

        $riskRegisterDeletePermission = Permission::create([
            'name' => 'risk_register_delete'
        ]);

        $riskRegisterAnalisaPermission = Permission::create([
            'name' => 'risk_register_analisa'
        ]);

        $riskRegisterPerencanaanPermission = Permission::create([
            'name' => 'risk_register_perencanaan'
        ]);

        $riskRegisterSendPermission = Permission::create([
            'name' => 'risk_register_send'
        ]);

        $riskRegisterAllUnitPermission = Permission::create([
            'name' => 'risk_register_all_unit'
        ]);

        $riskRegisterChildUnitPermission = Permission::create([
            'name' => 'risk_register_child_unit'
        ]);

        $rankingRisikoViewPermission = Permission::create([
            'name' => 'ranking_risiko_view'
        ]);

        $rankingRisikoCalculatePermission = Permission::create([
            'name' => 'ranking_risiko_calculate'
        ]);

        $rankingRisikoSendPermission = Permission::create([
            'name' => 'ranking_risiko_send'
        ]);

        $prioritasRisikoViewPermission = Permission::create([
            'name' => 'prioritas_risiko_view'
        ]);

        $prioritasRisikoVerificationPermission = Permission::create([
            'name' => 'prioritas_risiko_verification'
        ]);

        $riskMonitoringListPermission = Permission::create([
            'name' => 'risk_monitoring_list'
        ]);

        $riskMonitoringViewPermission = Permission::create([
            'name' => 'risk_monitoring_view'
        ]);

        $riskMonitoringInputPermission = Permission::create([
            'name' => 'risk_monitoring_input'
        ]);

        $lostEventListPermission = Permission::create([
            'name' => 'lost_event_list'
        ]);

        $lostEventViewPermission = Permission::create([
            'name' => 'lost_event_view'
        ]);

        $lostEventCreatePermission = Permission::create([
            'name' => 'lost_event_create'
        ]);

        $lostEventEditPermission = Permission::create([
            'name' => 'lost_event_edit'
        ]);

        $lostEventDeletePermission = Permission::create([
            'name' => 'lost_event_delete'
        ]);

        $userPermission = Permission::create([
            'name' => 'manajemen_user'
        ]);

        $unitPermission = Permission::create([
            'name' => 'manajemen_unit'
        ]);

        $masterPermission = Permission::create([
            'name' => 'manajemen_master'
        ]);

        $capaianTck = Permission::create([
            'name' => 'capaian_tck'
        ]);

        $capaianTkmru = Permission::create([
            'name' => 'capaian_tkmru'
        ]);

        $riskProfile = Permission::create([
            'name' => 'risk_profile'
        ]);

        $riskMapSetting = Permission::create([
            'name' => 'risk_map_setting'
        ]);

        $riskControlSetting = Permission::create([
            'name' => 'risk_control_setting'
        ]);

        $inputMenu = Permission::create([
            'name' => 'input_menu'
        ]);

        $riskStrategy = Permission::create([
            'name' => 'strategi_risiko'
        ]);

        $extraPermissions = Helper::syncBuiltInPermissions();

        $inputRole->givePermissionTo([$riskRegisterListPermission, $riskRegisterViewPermission, $riskRegisterCreatePermission, $riskRegisterEditPermission, $riskRegisterDeletePermission, $riskRegisterAnalisaPermission, $riskRegisterPerencanaanPermission, $riskRegisterSendPermission, $riskMonitoringListPermission, $riskMonitoringViewPermission, $riskMonitoringInputPermission, $lostEventListPermission, $lostEventViewPermission, $lostEventCreatePermission, $lostEventEditPermission, $lostEventDeletePermission, $riskProfile, $inputMenu]);

        $viewChildRole->givePermissionTo([$riskRegisterChildUnitPermission]);

        $validasiRole->givePermissionTo([$rankingRisikoViewPermission, $rankingRisikoCalculatePermission, $rankingRisikoSendPermission, $riskProfile]);

        $verifikatorRole->givePermissionTo([$prioritasRisikoViewPermission, $prioritasRisikoVerificationPermission, $riskProfile]);

        $viewRole->givePermissionTo($riskRegisterAllUnitPermission);

        $manajemen_risikoRole->givePermissionTo([$riskRegisterListPermission, $riskRegisterViewPermission, $riskRegisterCreatePermission, $riskRegisterEditPermission, $riskRegisterDeletePermission, $riskRegisterAnalisaPermission, $riskRegisterPerencanaanPermission, $riskRegisterSendPermission, $riskRegisterAllUnitPermission, $riskMonitoringListPermission, $riskMonitoringViewPermission, $riskMonitoringInputPermission, $rankingRisikoCalculatePermission, $rankingRisikoSendPermission, $prioritasRisikoVerificationPermission, $userPermission, $unitPermission, $masterPermission, $lostEventListPermission, $lostEventViewPermission, $lostEventCreatePermission, $lostEventEditPermission, $lostEventDeletePermission, $riskProfile]);

        $adminRole->givePermissionTo([$userPermission, $unitPermission, $riskRegisterAllUnitPermission, $riskMapSetting, $riskControlSetting,$masterPermission, ...$extraPermissions]);

        $tkmruRole->givePermissionTo([
            $capaianTkmru
        ]);

        $tckRole->givePermissionTo([
            $capaianTck
        ]);

        $riskStrategyRole->givePermissionTo([
            $riskStrategy
        ]);

        $userAdmin = User::create([
            'unit_type_id' => '1',
            'unit_id' => '1',
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
            'type' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userAdmin->assignRole($adminRole);

        $userProyek = User::create([
            'unit_type_id' => '2',
            'unit_id' => '2',
            'name' => 'User A',
            'email' => 'roa@mail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('123321'),
            'type' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userProyek->assignRole($inputRole);
    }
}
