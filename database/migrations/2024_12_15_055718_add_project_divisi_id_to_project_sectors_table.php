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
        Schema::table('project_sektors', function (Blueprint $table) {
            $table->foreignId('project_divisi_id')->constrained('project_divisis')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_sektors', function (Blueprint $table) {
            $table->dropForeign(['project_divisi_id']);
            $table->dropColumn('project_divisi_id');
        });
    }
};
