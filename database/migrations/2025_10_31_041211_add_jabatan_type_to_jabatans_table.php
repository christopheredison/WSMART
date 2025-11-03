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
        Schema::table('jabatans', function (Blueprint $table) {
            // 1: Bukan Project, 2: Project
            $table->tinyInteger('jabatan_type')
                  ->default(1)
                  ->after('description')
                  ->comment('1: Bukan Project, 2: Project');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jabatans', function (Blueprint $table) {
            $table->dropColumn('jabatan_type');
        });
    }
};
