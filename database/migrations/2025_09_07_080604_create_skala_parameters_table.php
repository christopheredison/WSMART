<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skala_parameters', function (Blueprint $table) {
            $table->id();
            $table->string('type_parameter');
            $table->integer('tingkat');
            $table->string('skala');
            $table->text('deskripsi');
            $table->decimal('min', 5, 2);
            $table->decimal('max', 5, 2);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skala_parameters');
    }
};