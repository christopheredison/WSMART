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
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->decimal('efektivitas_perlakuan_risiko', 8, 2)->nullable()->after('is_closed');
        });

        Schema::table('project_risks', function (Blueprint $table) {
            $table->decimal('efektivitas_perlakuan_risiko', 8, 2)->nullable()->after('is_closed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->dropColumn('efektivitas_perlakuan_risiko');
        });

        Schema::table('project_risks', function (Blueprint $table) {
            $table->dropColumn('efektivitas_perlakuan_risiko');
        });
    }
};
