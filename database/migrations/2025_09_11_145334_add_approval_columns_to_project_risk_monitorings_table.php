<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('status');
            $table->boolean('is_revision')->default(false)->after('is_approved');
        });
    }

    public function down()
    {
        Schema::table('project_risk_monitorings', function (Blueprint $table) {
            $table->dropColumn('is_approved');
            $table->dropColumn('is_revision');
        });
    }
};
