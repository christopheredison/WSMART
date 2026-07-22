<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('risk_contexts', function (Blueprint $table) {
            $table->string('status')->default('Draft')->after('asumsi_dasar'); 
            $table->text('catatan_perbaikan')->nullable()->after('status');
            $table->unsignedBigInteger('verified_by')->nullable()->after('catatan_perbaikan');
            $table->dateTime('verified_at')->nullable()->after('verified_by');
        });
    }

    public function down()
    {
        Schema::table('risk_contexts', function (Blueprint $table) {
            $table->dropColumn(['status', 'catatan_perbaikan', 'verified_by', 'verified_at']);
        });
    }
};