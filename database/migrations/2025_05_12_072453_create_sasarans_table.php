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
        Schema::create('sasarans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('periode_id');
            $table->unsignedBigInteger('metrik_strategi_risiko_id');
            $table->text('sasaran')->nullable();
            $table->decimal('expected_result', 16, 2);
            $table->decimal('risk_value', 16, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('periode_id')
                ->references('id')->on('periodes')
                ->onDelete('cascade');
            $table->foreign('metrik_strategi_risiko_id')
                ->references('id')->on('metrik_strategi_risikos')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sasarans');
    }
};
