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
        Schema::table('perlakuan_dampak_risikos', function (Blueprint $table) {
            $table->unsignedBigInteger('dampak_risiko_id')->nullable();
            $table->foreign('dampak_risiko_id')->references('id')->on('dampak_risiko_projects')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('perlakuan_dampak_risikos', function (Blueprint $table) {
            $table->dropForeign(['dampak_risiko_id']);
            $table->dropColumn('dampak_risiko_id');
        });
    }
};
