<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dimension_aspect_evaluations', function (Blueprint $table) {
            $table->foreignId('period_id')
                ->nullable()
                ->after('id')
                ->constrained('rmi_periods')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('dimension_aspect_evaluations', function (Blueprint $table) {
            $table->dropForeign(['period_id']);
            $table->dropColumn('period_id');
        });
    }
};
