<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->tinyInteger('request_edit')->default(0);
            $table->text('request_edit_reason')->nullable();
        });
    }

    public function down()
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->dropColumn(['request_edit', 'request_edit_reason']);
        });
    }
};