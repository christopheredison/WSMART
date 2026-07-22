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
    public function up()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->unsignedBigInteger('project_risk_id')->nullable()->after('identifikasi_risiko_id');
            $table->unsignedBigInteger('identifikasi_risiko_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropColumn('project_risk_id');
            $table->unsignedBigInteger('identifikasi_risiko_id')->nullable(false)->change();
        });
    }
};
