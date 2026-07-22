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
        Schema::table('dimension_aspect_evaluations', function (Blueprint $table) {
            //
            // Membuat sub_dimension_id menjadi nullable
            $table->foreignId('sub_dimension_id')->nullable()->change();
            
            // Menambahkan kolom dimension_id
            $table->foreignId('dimension_id')->nullable()->after('sub_dimension_id')
                  ->constrained('dimensions')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dimension_aspect_evaluations', function (Blueprint $table) {
            //
            // Menghapus kolom dimension_id
            $table->dropForeign(['dimension_id']);
            $table->dropColumn('dimension_id');
            
            // Mengembalikan sub_dimension_id menjadi tidak nullable
            $table->foreignId('sub_dimension_id')->nullable(false)->change();
        });
    }
};
