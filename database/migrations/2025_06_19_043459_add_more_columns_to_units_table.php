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
        Schema::table('units', function (Blueprint $table) {
            $table->string('unit_deskripsi')->nullable();
            $table->string('persubarea_sap')->nullable();
            $table->string('persubarea_deskripsi')->nullable();
            $table->string('persubarea_type')->nullable();
            $table->string('company_sap')->nullable();
            $table->string('company_deskripsi')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('cost_center_deskripsi')->nullable();
            $table->string('cost_center_abbrevation')->nullable();
            $table->string('cost_center_type')->nullable();
            $table->string('cost_center_parent')->nullable();
            $table->string('cost_center_parent_deskripsi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('unit_deskripsi');
            $table->dropColumn('persubarea_sap');
            $table->dropColumn('persubarea_deskripsi');
            $table->dropColumn('persubarea_type');
            $table->dropColumn('company_sap');
            $table->dropColumn('company_deskripsi');
            $table->dropColumn('cost_center');
            $table->dropColumn('cost_center_deskripsi');
            $table->dropColumn('cost_center_abbrevation');
            $table->dropColumn('cost_center_type');
            $table->dropColumn('cost_center_parent');
            $table->dropColumn('cost_center_parent_deskripsi');
        });
    }
};
