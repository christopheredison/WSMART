<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loss_event_project_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loss_event_project_id')
                  ->constrained('loss_event_projects')
                  ->onDelete('cascade');
            
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->integer('file_size')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loss_event_project_files');
    }
};