<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->foreignId('approval_step_id')->nullable()->after('risk_id')->constrained('approval_steps')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropForeign(['approval_step_id']);
            $table->dropColumn('approval_step_id');
        });
    }
};
