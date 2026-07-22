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
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->decimal('risk_limit', 10, 2)->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_periode_lists', function (Blueprint $table) {
            $table->dropColumn('risk_limit');
        });
    }
};
