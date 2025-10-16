<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpportunitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_risk_monitoring_id');
            $table->text('penjelasan_peluang_rencana')->nullable();
            $table->text('penjelasan_peluang_realisasi')->nullable();
            $table->decimal('nilai_peluang_rencana', 20, 2)->default(0);
            $table->decimal('nilai_peluang_realisasi', 20, 2)->default(0);
            $table->timestamps();

            $table->foreign('unit_risk_monitoring_id')
                  ->references('id')
                  ->on('unit_risk_monitorings')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opportunities');
    }
}