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
        Schema::create('strategi_bisnis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sasaran_id');
            $table->text('strategi')->nullable();
            $table->integer('status')->default(0)
                ->comment('0 = -, 1 = Accept, 2 = Avoid');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('sasaran_id')
                ->references('id')->on('sasarans')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strategi_bisnis');
    }
};
