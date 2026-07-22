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
        Schema::create('perlakuan_penyebab_risiko_unit_leds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('penyebab_risiko_led_id')->nullable();
            $table->text('rencana_perlakuan_risiko')->nullable();
            $table->text('output_perlakuan_risiko')->nullable();
            $table->decimal('biaya_perlakuan_risiko', 20, 2)->nullable();
            $table->string('pic')->nullable();
            $table->unsignedBigInteger('pic_jabatan_id')->nullable();
            $table->date('timeline_perlakuan_risiko_start')->nullable();
            $table->date('timeline_perlakuan_risiko_end')->nullable();
            $table->integer('opsi_perlakuan_risiko')->nullable();
            $table->integer('jenis_rencana_perlakuan_risiko')->nullable();
            $table->timestamps();
            
            $table->foreign('penyebab_risiko_led_id', 'fk_perlakuan_unit_leds_to_penyebab_unit_leds')
                  ->references('id')
                  ->on('penyebab_risiko_unit_leds')
                  ->onDelete('set null');
            $table->foreign('pic_jabatan_id')->references('id')->on('jabatans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perlakuan_penyebab_risiko_unit_leds');
    }
};
