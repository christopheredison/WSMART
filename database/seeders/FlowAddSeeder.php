<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use Illuminate\Support\Facades\DB;

class FlowAddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang ada terlebih dahulu untuk menghindari duplikasi
        DB::table('approval_steps')->where('approval_flow_id', 2)->delete();
        DB::table('approval_flows')->where('id', 2)->delete();
        DB::table('approval_steps')->where('approval_flow_id', 3)->delete();
        DB::table('approval_flows')->where('id', 3)->delete();
        
        // Insert data approval flow
        ApprovalFlow::create([
            'id' => 2,
            'unit_id' => 38,
            'project_id' => null,
            'min_verification' => 3,
            'created_at' => '2025-07-08 07:31:24',
            'updated_at' => '2025-07-08 07:31:24',
        ]);
        
        // Insert data approval steps
        ApprovalStep::create([
            'id' => 4,
            'approval_flow_id' => 2,
            'level_id' => 2,
            'step_order' => 1,
            'created_at' => '2025-07-08 07:32:07',
            'updated_at' => '2025-07-08 07:32:07',
        ]);
        
        ApprovalStep::create([
            'id' => 5,
            'approval_flow_id' => 2,
            'level_id' => 3,
            'step_order' => 2,
            'created_at' => '2025-07-08 07:32:25',
            'updated_at' => '2025-07-08 07:32:25',
        ]);
        
        ApprovalStep::create([
            'id' => 6,
            'approval_flow_id' => 2,
            'level_id' => 4,
            'step_order' => 3,
            'created_at' => '2025-07-08 07:32:39',
            'updated_at' => '2025-07-08 07:32:39',
        ]);

        // Insert data approval flow
        ApprovalFlow::create([
            'id' => 3,
            'unit_id' => 3,
            'project_id' => null,
            'min_verification' => 3,
            'created_at' => '2025-07-08 07:31:24',
            'updated_at' => '2025-07-08 07:31:24',
        ]);
        
        // Insert data approval steps
        ApprovalStep::create([
            'id' => 7,
            'approval_flow_id' => 3,
            'level_id' => 2,
            'step_order' => 1,
            'created_at' => '2025-07-08 07:32:07',
            'updated_at' => '2025-07-08 07:32:07',
        ]);
        
        ApprovalStep::create([
            'id' => 8,
            'approval_flow_id' => 3,
            'level_id' => 3,
            'step_order' => 2,
            'created_at' => '2025-07-08 07:32:25',
            'updated_at' => '2025-07-08 07:32:25',
        ]);
        
        ApprovalStep::create([
            'id' => 9,
            'approval_flow_id' => 3,
            'level_id' => 4,
            'step_order' => 3,
            'created_at' => '2025-07-08 07:32:39',
            'updated_at' => '2025-07-08 07:32:39',
        ]);
    }
}