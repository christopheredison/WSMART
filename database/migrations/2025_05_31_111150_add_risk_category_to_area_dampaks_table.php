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
        Schema::table('area_dampaks', function (Blueprint $table) {
            //
            $table->text('risk_category')
                  ->nullable()
                  ->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('area_dampaks', function (Blueprint $table) {
            //
            $table->dropColumn('risk_category');
        });
    }
};
