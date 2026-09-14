<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risk_impact_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_risk_analisa_id')
                ->constrained('project_risk_analisas')
                ->cascadeOnDelete();
            $table->string('uraian');
            $table->decimal('volume', 20, 4);
            $table->decimal('harga_satuan', 20, 2);
            $table->decimal('subtotal', 20, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risk_impact_details');
    }
};

