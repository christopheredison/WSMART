<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            $table->text('penilaian')->nullable();
            $table->integer('tipe_penilaian')
                  ->nullable()
                  ->comment('1: Eksternal, 2: Internal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('rmi_periods', function (Blueprint $table) {
            $table->dropColumn(['penilaian', 'tipe_penilaian']);
        });
    }
};