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
        Schema::create('data_batches', function (Blueprint $table) {
            $table->id();
            $table->integer('periode_id');
            $table->integer('unit_id');
            $table->integer('batch')->default(1);
            $table->integer('status')->comment('1: proses, 2: kirim, 3: ranking, 4: verifikasi, 5: revisi, 6: utama, 7: verifikasi universitas, 8: finish');
            $table->boolean('finish')->default(false);
            $table->timestamps();

             // Menambahkan constraint unique
             $table->unique(['periode_id', 'unit_id', 'batch'], 'unique_periode_unit_batch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_batches');
    }
};
