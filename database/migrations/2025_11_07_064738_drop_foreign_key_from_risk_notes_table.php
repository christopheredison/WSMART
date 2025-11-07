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
        Schema::table('risk_notes', function (Blueprint $table) {
            $table->dropForeign(['risiko_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_notes', function (Blueprint $table) {
            $table->foreign('risiko_id')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');
        });
    }
};
