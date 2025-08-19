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
        Schema::table('project_risks', function (Blueprint $table) {
            // Tambahkan kolom sasaran_proyek_id yang nullable
            $table->unsignedBigInteger('sasaran_proyek_id')->nullable();
            
            // Tambahkan foreign key constraint
            $table->foreign('sasaran_proyek_id')
                  ->references('id')
                  ->on('sasaran_proyeks')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('project_risks', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['sasaran_proyek_id']);
            
            // Kemudian hapus kolom
            $table->dropColumn('sasaran_proyek_id');
        });
    }
};
