<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            if (! Schema::hasColumn('audits', 'rmi_period_id')) {
                $table->unsignedBigInteger('rmi_period_id')->nullable()->index()->after('unit_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audits', function (Blueprint $table) {
            if (Schema::hasColumn('audits', 'rmi_period_id')) {
                $table->dropIndex(['rmi_period_id']);
                $table->dropColumn('rmi_period_id');
            }
        });
    }
};
