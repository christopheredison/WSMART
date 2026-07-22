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
        Schema::create('parameter_kinerjas', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();       // misal "2a", atau null untuk top-level
            $table->string('name');
            $table->decimal('weight', 5, 2);          // bobot dalam persen, misal 30.00
            $table->foreignId('parent_id')            // untuk sub-parameter; null = top-level
                  ->nullable()
                  ->constrained('parameter_kinerjas')
                  ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parameter_kinerjas');
    }
};
