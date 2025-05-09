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
        Schema::create('loss_event_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loss_event_id')->nullable();
            $table->foreign('loss_event_id')->references('id')->on('loss_events');
            $table->text('rekomendasi_perbaikan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_event_children');
    }
};
