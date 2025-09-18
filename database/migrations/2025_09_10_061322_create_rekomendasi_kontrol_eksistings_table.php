<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('rekomendasi_kontrol_eksistings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekomendasi_risiko_id')->constrained('rekomendasi_risikos')->onDelete('cascade');
            $table->text('kontrol_eksisting');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rekomendasi_kontrol_eksistings');
    }
};