<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('risk_monitoring_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('risiko_id');
            $table->integer('type')->comment('1: Divisi, 2: Project');
            $table->integer('status')->comment('1 = diterima/verifikasi, 2 = ditolak/revisi, 3 = pengajuan');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('quarter')->nullable();
            $table->integer('month')->nullable();
            $table->year('year')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('risk_monitoring_notes');
    }
};