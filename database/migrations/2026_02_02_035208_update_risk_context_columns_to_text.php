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
        // ==========================================
        // GROUP 1: Project Risk Context (Request Sebelumnya)
        // ==========================================

        Schema::table('project_risk_context_members', function (Blueprint $table) {
            $table->text('nama')->change();
        });

        Schema::table('project_risk_context_stakeholder_internals', function (Blueprint $table) {
            $table->text('stakeholder')->change();
            $table->text('peran')->change();
            $table->text('komunikasi')->change();
        });

        Schema::table('project_risk_context_stakeholder_externals', function (Blueprint $table) {
            $table->text('stakeholder')->change();
            $table->text('peran')->change();
            $table->text('komunikasi')->change();
        });

        // ==========================================
        // GROUP 2: Risk Context (Request Terbaru)
        // ==========================================

        Schema::table('risk_context_members', function (Blueprint $table) {
            $table->text('nama')->change();
        });

        Schema::table('risk_context_stakeholder_internals', function (Blueprint $table) {
            $table->text('stakeholder')->change();
            $table->text('peran')->change();
            $table->text('komunikasi')->change();
        });

        Schema::table('risk_context_stakeholder_externals', function (Blueprint $table) {
            $table->text('stakeholder')->change();
            $table->text('peran')->change();
            $table->text('komunikasi')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Mengembalikan semua kolom ke tipe String (Varchar 255)

        // Revert Group 2
        Schema::table('risk_context_stakeholder_externals', function (Blueprint $table) {
            $table->string('stakeholder')->change();
            $table->string('peran')->change();
            $table->string('komunikasi')->change();
        });

        Schema::table('risk_context_stakeholder_internals', function (Blueprint $table) {
            $table->string('stakeholder')->change();
            $table->string('peran')->change();
            $table->string('komunikasi')->change();
        });

        Schema::table('risk_context_members', function (Blueprint $table) {
            $table->string('nama')->change();
        });

        // Revert Group 1
        Schema::table('project_risk_context_stakeholder_externals', function (Blueprint $table) {
            $table->string('stakeholder')->change();
            $table->string('peran')->change();
            $table->string('komunikasi')->change();
        });

        Schema::table('project_risk_context_stakeholder_internals', function (Blueprint $table) {
            $table->string('stakeholder')->change();
            $table->string('peran')->change();
            $table->string('komunikasi')->change();
        });

        Schema::table('project_risk_context_members', function (Blueprint $table) {
            $table->string('nama')->change();
        });
    }
};
