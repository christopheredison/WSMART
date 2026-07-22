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
        Schema::create('ict_dos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_control_id')->nullable()->constrained('ict_plan_controls')->onDelete('cascade');
            $table->integer('jenis_kontrol')->nullable()->comment('1: kontrol operasi, 2: kontrol kepatuhan, 3: kontrol pelaporan');
            $table->integer('bentuk_kontrol')->nullable()->comment('1: SOP, 2: Kebijakan, 3:Sistem Informasi dan Komunikasi, 4: Sistem Lainnya');
            $table->integer('level_pengendalian')->nullable()->comment('1: Entitas, 2: Operasional');
            $table->integer('kecukupan_desain_pengendalian_1')->nullable()->comment('1: cukup, 2: tidak cukup');
            $table->integer('kecukupan_desain_pengendalian_2')->nullable()->comment('1: cukup, 2: tidak cukup');
            $table->integer('kecukupan_desain_pengendalian_3')->nullable()->comment('1: cukup, 2: tidak cukup');
            $table->integer('kecukupan_desain_pengendalian_4')->nullable()->comment('1: cukup, 2: tidak cukup');
            $table->integer('kecukupan_desain_pengendalian_akhir')->nullable()->comment('1: cukup, 2: tidak cukup');
            $table->integer('efektivitas_desain_pengendalian_1')->nullable()->comment('1: efektif, 2: efektif sebagian, 3: tidak efektif');
            $table->integer('efektivitas_desain_pengendalian_2')->nullable()->comment('1: efektif, 2: efektif sebagian, 3: tidak efektif');
            $table->integer('efektivitas_desain_pengendalian_3')->nullable()->comment('1: efektif, 2: efektif sebagian, 3: tidak efektif');
            $table->integer('efektivitas_desain_pengendalian_4')->nullable()->comment('1: efektif, 2: efektif sebagian, 3: tidak efektif');
            $table->integer('efektivitas_desain_pengendalian_akhir')->nullable()->comment('1: efektif, 2: efektif sebagian, 3: tidak efektif');
            $table->text('kesimpulan_akhir')->nullable();
            $table->text('hasil_temuan')->nullable();
            $table->text('rencana_tindak_lanjut')->nullable();
            $table->date('batas_waktu_penyelesaian')->nullable();
            $table->text('penanggung_jawab')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ict_dos');
    }
};
