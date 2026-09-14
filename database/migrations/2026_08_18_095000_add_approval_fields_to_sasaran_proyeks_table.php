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
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            if (!Schema::hasColumn('sasaran_proyeks', 'project_periode_list_id')) {
                $table->unsignedBigInteger('project_periode_list_id')->nullable()->after('costcenter_code');
            }

            if (!Schema::hasColumn('sasaran_proyeks', 'requested_by')) {
                $table->unsignedBigInteger('requested_by')->nullable()->after('project_periode_list_id');
            }

            if (!Schema::hasColumn('sasaran_proyeks', 'approval_status')) {
                $table->tinyInteger('approval_status')
                    ->default(1)
                    ->comment('0 = pending, 1 = approved, 2 = rejected')
                    ->after('status');
            }

            if (!Schema::hasColumn('sasaran_proyeks', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('approval_status');
            }

            if (!Schema::hasColumn('sasaran_proyeks', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verified_by');
            }

            if (!Schema::hasColumn('sasaran_proyeks', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('verified_at');
            }
        });

        DB::table('sasaran_proyeks')
            ->whereNull('approval_status')
            ->update(['approval_status' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            $columns = [
                'project_periode_list_id',
                'requested_by',
                'approval_status',
                'verified_by',
                'verified_at',
                'rejected_reason',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('sasaran_proyeks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
