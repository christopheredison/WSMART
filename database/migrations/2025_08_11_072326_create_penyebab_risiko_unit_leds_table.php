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
        Schema::create('penyebab_risiko_unit_leds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loss_event_unit_id');
            $table->text('penyebab_risiko');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penyebab_risiko_unit_leds');
    }
};
