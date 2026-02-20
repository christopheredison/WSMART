<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('nilai_ok_porsi', 20, 2)->nullable()->after('nk');
        });

        Schema::table('project_hasil_usaha', function (Blueprint $table) {
            $table->decimal('kontrak_review_total', 20, 2)->nullable()->after('period');
        });
    }

    public function down()
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('nilai_ok_porsi');
        });

        Schema::table('project_hasil_usaha', function (Blueprint $table) {
            $table->dropColumn('kontrak_review_total');
        });
    }
};
