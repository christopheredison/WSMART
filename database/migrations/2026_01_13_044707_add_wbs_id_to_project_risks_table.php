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
        Schema::table('project_risks', function (Blueprint $table) {
            $table->unsignedBigInteger('wbs_id')->nullable()->after('peristiwa_risiko_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risks', function (Blueprint $table) {
            $table->dropColumn('wbs_id');
        });
    }
};
