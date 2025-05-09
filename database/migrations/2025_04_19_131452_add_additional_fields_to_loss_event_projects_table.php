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
        Schema::table('loss_event_projects', function (Blueprint $table) {
            $table->integer('project_id')->nullable()->after('project_sektor_id');
            $table->date('tanggal_kejadian')->nullable()->after('project_id');
            $table->date('rentang_kejadian_awal')->nullable()->after('tanggal_kejadian');
            $table->date('rentang_kejadian_akhir')->nullable()->after('rentang_kejadian_awal');
            $table->decimal('nilai_kerugian_finansial', 15, 2)->nullable()->after('rentang_kejadian_akhir');
            $table->text('nilai_kerugian_non_finansial')->nullable()->after('nilai_kerugian_finansial');
            $table->string('unit_penanggung_jawab')->nullable()->after('nilai_kerugian_non_finansial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loss_event_projects', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_kejadian',
                'rentang_kejadian_awal',
                'rentang_kejadian_akhir',
                'nilai_kerugian_finansial',
                'nilai_kerugian_non_finansial',
                'unit_penanggung_jawab'
            ]);
        });
    }
};