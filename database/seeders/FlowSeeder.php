<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStep;
use Illuminate\Support\Facades\DB;

class FlowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data yang ada terlebih dahulu untuk menghindari duplikasi
        DB::table('approval_steps')->where('approval_flow_id', 1)->delete();
        DB::table('approval_flows')->where('id', 1)->delete();
        
        // Insert data approval flow
        ApprovalFlow::create([
            'id' => 1,
            'unit_id' => 2,
            'project_id' => null,
            'min_verification' => 3,
            'created_at' => '2025-07-08 07:31:24',
            'updated_at' => '2025-07-08 07:31:24',
        ]);
        
        // Insert data approval steps
        ApprovalStep::create([
            'id' => 1,
            'approval_flow_id' => 1,
            'level_id' => 2,
            'step_order' => 1,
            'created_at' => '2025-07-08 07:32:07',
            'updated_at' => '2025-07-08 07:32:07',
        ]);
        
        ApprovalStep::create([
            'id' => 2,
            'approval_flow_id' => 1,
            'level_id' => 3,
            'step_order' => 2,
            'created_at' => '2025-07-08 07:32:25',
            'updated_at' => '2025-07-08 07:32:25',
        ]);
        
        ApprovalStep::create([
            'id' => 3,
            'approval_flow_id' => 1,
            'level_id' => 4,
            'step_order' => 3,
            'created_at' => '2025-07-08 07:32:39',
            'updated_at' => '2025-07-08 07:32:39',
        ]);
    }
}