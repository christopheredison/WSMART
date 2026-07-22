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
        Schema::table('ict_plans', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'rejected'])
                  ->default('draft')
                  ->after('metode_pengujian');

            $table->text('rejection_reason')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ict_plans', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('rejection_reason');
        });
    }
};