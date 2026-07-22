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
        Schema::table('taksonomi_risikos', function (Blueprint $table) {
            $table->renameColumn('image', 'nama');
            $table->text('deskripsi')->after('image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('taksonomi_risikos', function (Blueprint $table) {
            $table->renameColumn('nama', 'image');
            $table->dropColumn('deskripsi');
        });
    }
};
