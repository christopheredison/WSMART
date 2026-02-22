<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
            if (!Schema::hasColumn('projects', 'biaya_perlakuan_risiko_rkp')) {
                $table->decimal('biaya_perlakuan_risiko_rkp', 22, 2)->nullable()->default(0)->after('nk');
            }
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'biaya_perlakuan_risiko_rkp')) {
                $table->dropColumn('biaya_perlakuan_risiko_rkp');
            }
        });
    }
};
