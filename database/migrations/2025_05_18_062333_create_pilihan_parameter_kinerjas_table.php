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
        Schema::create('pilihan_parameter_kinerjas', function (Blueprint $table) {
            $table->id();
            // Foreign key ke tabel parameter_kinerjas
            $table->foreignId('parameter_id')
                    ->constrained('parameter_kinerjas')
                    ->cascadeOnDelete();

            $table->string('code', 1);            // 'a','b','c','d'
            $table->text('description');          // teks lengkap pilihan
            $table->unsignedTinyInteger('scale'); // nilai skala, misal 4,3,2,1
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pilihan_parameter_kinerjas');
    }
};
