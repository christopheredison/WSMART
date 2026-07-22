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
        Schema::create('risk_contexts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('periode_id')->constrained('periodes')->onDelete('cascade');
            
            // Informasi Umum
            $table->text('nilai')->nullable();
            $table->foreignId('pimpinan_tertinggi_jabatan_id')->nullable()->constrained('jabatans')->onDelete('set null');
            $table->text('sponsor')->nullable();
            
            // Ruang Lingkup
            $table->text('deskripsi')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('lingkup_pekerjaan')->nullable();
            $table->text('pekerjaan_luar_lingkup')->nullable();
            
            // Konteks
            $table->text('sasaran')->nullable();
            $table->text('batasan')->nullable();
            $table->text('asumsi_dasar')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Unique constraint untuk unit dan periode
            $table->unique(['unit_id', 'periode_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_contexts');
    }
};
