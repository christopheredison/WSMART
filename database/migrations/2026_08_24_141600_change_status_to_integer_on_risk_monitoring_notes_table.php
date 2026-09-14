<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // boolean true/false → integer 1/0, lalu samakan penolakan dengan RiskNote (2)
            DB::statement('ALTER TABLE risk_monitoring_notes ALTER COLUMN status DROP DEFAULT');
            DB::statement('ALTER TABLE risk_monitoring_notes ALTER COLUMN status TYPE integer USING (CASE WHEN status THEN 1 ELSE 0 END)');
            DB::statement("COMMENT ON COLUMN risk_monitoring_notes.status IS '1 = diterima/verifikasi, 2 = ditolak/revisi, 3 = pengajuan'");
        } else {
            DB::statement('ALTER TABLE risk_monitoring_notes MODIFY status TINYINT NOT NULL COMMENT "1 = diterima/verifikasi, 2 = ditolak/revisi, 3 = pengajuan"');
        }

        DB::table('risk_monitoring_notes')->where('status', 0)->update(['status' => 2]);
    }

    public function down(): void
    {
        DB::table('risk_monitoring_notes')->where('status', 2)->update(['status' => 0]);
        DB::table('risk_monitoring_notes')->where('status', '>', 1)->update(['status' => 1]);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE risk_monitoring_notes ALTER COLUMN status TYPE boolean USING (status = 1)');
            DB::statement("COMMENT ON COLUMN risk_monitoring_notes.status IS '1: Diterima, 0: Ditolak'");
        } else {
            DB::statement('ALTER TABLE risk_monitoring_notes MODIFY status BOOLEAN NOT NULL COMMENT "1: Diterima, 0: Ditolak"');
        }
    }
};
