<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan nama tabel 'unit_hasil_usaha'
        Schema::create('unit_hasil_usaha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->onDelete('cascade');
            $table->string('cost_center')->nullable();
            $table->string('period', 6); // Format YYYYMM
            $table->decimal('kontrak_review', 20, 2)->default(0);
            $table->decimal('penjualan_ra', 20, 2)->default(0);
            $table->decimal('penjualan_ri', 20, 2)->default(0);
            $table->double('progress_fisik_ra')->default(0);
            $table->double('progress_fisik_ri')->default(0);
            $table->decimal('lsp_review', 20, 2)->default(0);
            $table->decimal('lsp_ra', 20, 2)->default(0);
            $table->decimal('lsp_ri', 20, 2)->default(0);
            $table->decimal('lsp_proyeksi', 20, 2)->default(0);
            $table->json('response_data')->nullable();
            $table->timestamps();

            // Kunci unik untuk data per unit per periode
            $table->unique(['unit_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_hasil_usaha');
    }
};