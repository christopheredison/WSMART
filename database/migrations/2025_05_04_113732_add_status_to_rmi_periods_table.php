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
        Schema::table('rmi_periods', function (Blueprint $table) {
            $table->integer('status')
                  ->nullable()
                  ->default(1)
                  ->comment('1=Dalam Proses, 2=Selesai')
                  ->after('score_rmi_desc');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
