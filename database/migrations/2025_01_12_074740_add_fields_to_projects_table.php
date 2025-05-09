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
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->string('kode_tender', 255)->nullable()->after('project_status');
            $table->text('nama_tender')->nullable()->after('kode_tender');
            $table->integer('tender_status')->nullable()->after('nama_tender');
            $table->unsignedBigInteger('project_type_id')->nullable()->after('tender_status');
            $table->string('owner', 255)->nullable()->after('project_type_id');
            $table->integer('owner_category')->nullable()->after('owner');
            $table->integer('sumber_dana')->nullable()->after('owner_category');
            $table->unsignedBigInteger('project_location_id')->nullable()->after('sumber_dana');
            $table->integer('jenis_kontrak')->nullable()->after('project_location_id');
            $table->integer('cara_pembayaran')->nullable()->after('jenis_kontrak');
            $table->integer('scope_pekerjaan')->nullable()->after('cara_pembayaran');
            $table->decimal('nk_ppn', 15, 2)->nullable()->after('scope_pekerjaan');
            $table->date('masa_pelaksanaan_start')->nullable()->after('nk_ppn');
            $table->date('masa_pelaksanaan_end')->nullable()->after('masa_pelaksanaan_start');
            $table->decimal('rapt', 15, 2)->nullable()->after('masa_pelaksanaan_end');
            $table->integer('rapt_persentase')->nullable()->after('rapt');
            $table->decimal('rapk_0_10_rp', 15, 2)->nullable()->after('rapt_persentase');
            $table->integer('rapk_0_10_persen')->nullable()->after('rapk_0_10_rp');
            $table->decimal('rapk_30_50_rp', 15, 2)->nullable()->after('rapk_0_10_persen');
            $table->integer('rapk_30_50_persen')->nullable()->after('rapk_30_50_rp');
            $table->decimal('rapk_70_90_rp', 15, 2)->nullable()->after('rapk_30_50_persen');
            $table->integer('rapk_70_90_persen')->nullable()->after('rapk_70_90_rp');
            $table->decimal('rapk_100_rp', 15, 2)->nullable()->after('rapk_70_90_persen');
            $table->integer('rapk_100_persen')->nullable()->after('rapk_100_rp');

            // Foreign key constraints
            $table->foreign('project_type_id')->references('id')->on('project_types')->onDelete('set null');
            $table->foreign('project_location_id')->references('id')->on('project_locations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            //
            $table->dropColumn([
                'kode_tender',
                'nama_tender',
                'tender_status',
                'project_type_id',
                'owner',
                'owner_category',
                'sumber_dana',
                'project_location_id',
                'jenis_kontrak',
                'cara_pembayaran',
                'scope_pekerjaan',
                'nk_ppn',
                'masa_pelaksanaan_start',
                'masa_pelaksanaan_end',
                'rapt',
                'rapt_persentase',
                'rapk_0_10_rp',
                'rapk_0_10_persen',
                'rapk_30_50_rp',
                'rapk_30_50_persen',
                'rapk_70_90_rp',
                'rapk_70_90_persen',
                'rapk_100_rp',
                'rapk_100_persen',
            ]);

            // Drop foreign key constraints
            $table->dropForeign(['project_type_id']);
            $table->dropForeign(['project_location_id']);
        });
    }
};
