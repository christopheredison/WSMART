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
        Schema::table('projects', function (Blueprint $table) {
            $table->string('project_code')->nullable()->after('id')->unique();
            $table->json('meta')->nullable()->after('project_sektor_id');
            $table->dropColumn('project_divisi_id');
            $table->dropColumn('project_sektor_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('project_divisi_id')->nullable()->constrained();
            $table->foreignId('project_sektor_id')->nullable()->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropUnique('projects_project_code_unique');
            $table->dropColumn('project_code');
            $table->dropColumn('meta');

            $table->dropConstrainedForeignId('project_divisi_id');
            $table->dropConstrainedForeignId('project_sektor_id');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->integer('project_divisi_id');
            $table->integer('project_sektor_id');
        });
    }
};
