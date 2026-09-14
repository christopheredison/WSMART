<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('peristiwa_risikos', function (Blueprint $table) {
            if (!Schema::hasColumn('peristiwa_risikos', 'project_id')) {
                $table->unsignedBigInteger('project_id')->nullable()->after('type');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'project_periode_list_id')) {
                $table->unsignedBigInteger('project_periode_list_id')->nullable()->after('project_id');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'requested_by')) {
                $table->unsignedBigInteger('requested_by')->nullable()->after('project_periode_list_id');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'status')) {
                $table->tinyInteger('status')
                    ->default(1)
                    ->comment('1 = master, 2 = pengajuan proyek')
                    ->after('requested_by');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'approval_status')) {
                $table->tinyInteger('approval_status')
                    ->default(1)
                    ->comment('0 = pending, 1 = approved, 2 = rejected')
                    ->after('status');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('approval_status');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }

            if (!Schema::hasColumn('peristiwa_risikos', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('verified_at');
            }
        });

        DB::table('peristiwa_risikos')
            ->whereNull('approval_status')
            ->update(['approval_status' => 1]);

        DB::table('peristiwa_risikos')
            ->whereNull('status')
            ->update(['status' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peristiwa_risikos', function (Blueprint $table) {
            $columns = [
                'project_id',
                'project_periode_list_id',
                'requested_by',
                'status',
                'approval_status',
                'verified_by',
                'verified_at',
                'rejected_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('peristiwa_risikos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
