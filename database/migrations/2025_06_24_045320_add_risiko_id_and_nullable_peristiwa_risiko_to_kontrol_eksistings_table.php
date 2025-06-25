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
        Schema::table('kontrol_eksistings', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('risiko_id')
                  ->after('peristiwa_risiko_id')
                  ->nullable(false);

            $table->foreign('risiko_id')
                  ->references('id')
                  ->on('identifikasi_risikos')
                  ->onDelete('cascade');

            $table->unsignedBigInteger('peristiwa_risiko_id')
                  ->nullable()
                  ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrol_eksistings', function (Blueprint $table) {
            //
            // rollback FK & kolom risiko_id
            $table->dropForeign(['risiko_id']);
            $table->dropColumn('risiko_id');

            // kembalikan peristiwa_risiko_id jadi not null
            $table->unsignedBigInteger('peristiwa_risiko_id')
                  ->nullable(false)
                  ->change();   
        });
    }
};
