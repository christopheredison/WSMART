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
        Schema::create('data_batch_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('data_batch_id');
            $table->text('notes');
            $table->integer('step_order');
            $table->boolean('unread')->default(true);
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            
            $table->foreign('data_batch_id')->references('id')->on('data_batches');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_batch_notes');
    }
};
