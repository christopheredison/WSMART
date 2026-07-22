<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risk_contexts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')
                  ->constrained('projects')
                  ->onDelete('cascade');

            // Kolom lainnya sama
            $table->text('nilai')->nullable();
            $table->foreignId('pimpinan_tertinggi_jabatan_id')->nullable()->constrained('jabatans')->onDelete('set null');
            $table->text('sponsor')->nullable();
            $table->text('deskripsi')->nullable();
            $table->text('tujuan')->nullable();
            $table->text('lingkup_pekerjaan')->nullable();
            $table->text('pekerjaan_luar_lingkup')->nullable();
            $table->text('sasaran')->nullable();
            $table->text('batasan')->nullable();
            $table->text('asumsi_dasar')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Ganti unique constraint
            $table->unique('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risk_contexts');
    }
};
