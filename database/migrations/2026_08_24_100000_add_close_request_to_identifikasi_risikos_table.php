<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->tinyInteger('close_request')->default(0)->after('closed_at');
            $table->text('close_request_reason')->nullable()->after('close_request');
            $table->timestamp('close_requested_at')->nullable()->after('close_request_reason');
            $table->unsignedBigInteger('close_requested_by')->nullable()->after('close_requested_at');
        });
    }

    public function down()
    {
        Schema::table('identifikasi_risikos', function (Blueprint $table) {
            $table->dropColumn([
                'close_request',
                'close_request_reason',
                'close_requested_at',
                'close_requested_by',
            ]);
        });
    }
};
