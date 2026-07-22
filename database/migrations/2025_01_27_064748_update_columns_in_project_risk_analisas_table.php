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
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            // Mengubah tipe data kolom menjadi DECIMAL(20,2)
            $table->decimal('nilai_dampak', 20, 2)->nullable()->change();
            $table->decimal('nilai_dampak_residual', 20, 2)->nullable()->change();
            $table->decimal('eksposur_risiko', 20, 2)->nullable()->change();
            $table->decimal('eksposur_risiko_residual', 20, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_risk_analisas', function (Blueprint $table) {
            // Mengembalikan tipe data kolom ke INT
            $table->integer('nilai_dampak')->change();
            $table->integer('nilai_dampak_residual')->change();
            $table->integer('eksposur_risiko')->change();
            $table->integer('eksposur_risiko_residual')->change();
        });
    }
};
