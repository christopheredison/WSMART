<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('unit_risk_monitorings', function (Blueprint $table) {
            $table->tinyInteger('status')->default(1)->after('month');
            $table->boolean('is_approved')->default(false)->after('status');
            $table->boolean('is_revision')->default(false)->after('is_approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('unit_risk_monitorings', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_approved', 'is_revision']);
        });
    }
};
