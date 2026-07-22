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
        Schema::create('project_hasil_usaha', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('profit_center');
            $table->string('period', 6); // Format YYYYMM
            $table->decimal('kontrak_review', 20, 2)->default(0);
            $table->double('progress_fisik_ra')->default(0);
            $table->double('progress_fisik_ri')->default(0);
            $table->decimal('penjualan_ra', 20, 2)->default(0);
            $table->decimal('penjualan_ri', 20, 2)->default(0);
            $table->decimal('lsp_review', 20, 2)->default(0);
            $table->decimal('lsp_ra', 20, 2)->default(0);
            $table->decimal('lsp_ri', 20, 2)->default(0);
            $table->decimal('lsp_proyeksi', 20, 2)->default(0);
            $table->json('response_data')->nullable();
            $table->timestamps();

            // Index unik agar tidak ada duplikasi data
            $table->unique(['profit_center', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_hasil_usaha');
    }
};