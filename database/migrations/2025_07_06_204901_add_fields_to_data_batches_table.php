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
        Schema::table('data_batches', function (Blueprint $table) {
            //
            // Mengubah periode_id menjadi nullable
            $table->integer('periode_id')->nullable()->change();
            
            // Menambahkan kolom type setelah periode_id
            $table->integer('type')->default(1)->after('periode_id')
                  ->comment('1: divisi, 2: project, 3: anak perusahaan');
            
            // Mengubah unit_id menjadi nullable
            $table->integer('unit_id')->nullable()->change();
            
            // Menambahkan kolom project_id setelah unit_id
            $table->integer('project_id')->nullable()->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('data_batches', function (Blueprint $table) {
            //
            // Menghapus kolom type dan project_id
            $table->dropColumn(['type', 'project_id']);
            
            // Mengembalikan periode_id dan unit_id menjadi tidak nullable
            $table->integer('periode_id')->nullable(false)->change();
            $table->integer('unit_id')->nullable(false)->change();
        });
    }
};
