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
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            //
            $table->integer('status')->nullable()->comment('1 = API dan 2 = Input');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sasaran_proyeks', function (Blueprint $table) {
            //
            $table->dropColumn('status');
        });
    }
};
