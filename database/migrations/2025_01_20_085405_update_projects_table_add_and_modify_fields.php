<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            Schema::table('projects', function (Blueprint $table) {
                // Adding new fields
                $table->decimal('rapk', 15, 2)->nullable()->after('rapt_persentase');
                $table->decimal('rapk_persentase', 6, 2)->nullable()->after('rapk');
    
                // Modifying existing fields
                $table->decimal('rapt_persentase', 6, 2)->nullable()->change();
                $table->decimal('rapk_0_10_persen', 6, 2)->nullable()->change();
                $table->decimal('rapk_30_50_persen', 6, 2)->nullable()->change();
                $table->decimal('rapk_70_90_persen', 6, 2)->nullable()->change();
                $table->decimal('rapk_100_persen', 6, 2)->nullable()->change();
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            // Dropping the newly added fields
            $table->dropColumn('rapk');
            $table->dropColumn('rapk_persentase');

            // Reverting the modified fields to integer
            $table->integer('rapt_persentase')->change();
            $table->integer('rapk_0_10_persen')->change();
            $table->integer('rapk_30_50_persen')->change();
            $table->integer('rapk_70_90_persen')->change();
            $table->integer('rapk_100_persen')->change();
        });
    }
};
