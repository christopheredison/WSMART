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
        Schema::table('loss_events', function (Blueprint $table) {
            $table->string('nilai_kerugian_finansial')->nullable()->change();
            $table->string('nilai_kerugian_non_fungsional')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_events', function (Blueprint $table) {
            $table->string('nilai_kerugian_finansial')->nullable(false)->change();
            $table->string('nilai_kerugian_non_fungsional')->nullable(false)->change();
        });
    }
};
