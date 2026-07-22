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
        Schema::create('risk_context_stakeholder_internals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_context_id')->constrained('risk_contexts')->onDelete('cascade');
            $table->string('stakeholder');
            $table->string('peran');
            $table->string('komunikasi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('risk_context_stakeholder_internals');
    }
};
