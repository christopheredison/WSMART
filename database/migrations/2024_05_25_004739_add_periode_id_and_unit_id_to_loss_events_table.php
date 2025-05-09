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
            //
            $table->integer('periode_id')->after('id');
            $table->integer('unit_id')->after('periode_id');
            $table->integer('user_id')->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_events', function (Blueprint $table) {
            //
            $table->dropColumn('periode_id');
            $table->dropColumn('unit_id');
            //$table->integer('user_id');
        });
    }
};
