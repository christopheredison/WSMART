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
        Schema::table('risk_limits', function (Blueprint $table) {
            //
            // Ubah 'sikap_risiko' (integer) menjadi nullable
            $table->integer('sikap_risiko')->nullable()->change();

            // Ubah 'persentase_limit' (decimal(5,2)) menjadi nullable
            $table->decimal('persentase_limit', 5, 2)->nullable()->change();

            // Ubah 'nominal_limit' (decimal(15,2)) menjadi nullable
            $table->decimal('nominal_limit', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('risk_limits', function (Blueprint $table) {
            //
            // Revert 'sikap_risiko' ke NOT NULL
            $table->integer('sikap_risiko')->nullable(false)->change();

            // Revert 'persentase_limit' ke NOT NULL
            $table->decimal('persentase_limit', 5, 2)->nullable(false)->change();

            // Revert 'nominal_limit' ke NOT NULL
            $table->decimal('nominal_limit', 15, 2)->nullable(false)->change();
        });
    }
};
