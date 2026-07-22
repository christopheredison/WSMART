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
        Schema::create('project_periode_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('periode_id')->constrained('periodes')->cascadeOnDelete();
            $table->foreignId('unit_id')->constrained('units')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('project_risks', function (Blueprint $table) {
            $table->foreignId('project_periode_list_id')->nullable()->constrained('project_periode_lists')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('project_periode_list_id');
        });
        Schema::dropIfExists('project_periode_lists');
    }
};
