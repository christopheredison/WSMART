<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parameter_kinerja_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penilaian_capaian_kinerja_id')
                  ->constrained('penilaian_capaian_kinerjas')
                  ->onDelete('cascade')
                  ->name('fk_pk_docs_penilaian_id');
            
            $table->foreignId('parameter_id')
                  ->constrained('parameter_kinerjas')
                  ->onDelete('cascade');

            $table->string('filename');
            $table->string('file_path');
            $table->string('mimetype')->nullable();
            $table->string('description')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parameter_kinerja_documents');
    }
};