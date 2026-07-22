<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected function getDriver(): string
    {
        return DB::connection()->getDriverName();
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        $driver = $this->getDriver();

        if ($driver === 'pgsql') {
            // PostgreSQL perlu konversi eksplisit dari boolean ke numeric.
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision DROP DEFAULT');
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision TYPE SMALLINT USING (CASE WHEN is_revision=true THEN 1 ELSE 0 END)');
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision SET DEFAULT 0');
            return;
        }

        if ($driver === 'mysql') {
            // Di MySQL boolean direpresentasikan sebagai TINYINT(1), jadi cukup ubah tipenya.
            DB::statement('ALTER TABLE unit_risk_monitorings MODIFY COLUMN is_revision SMALLINT NOT NULL DEFAULT 0');
            return;
        }

        throw new \RuntimeException("Driver database '{$driver}' belum didukung untuk migration ini.");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $driver = $this->getDriver();

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision DROP DEFAULT');
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision TYPE BOOLEAN USING (CASE WHEN is_revision > 0 THEN true ELSE false END)');
            DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision SET DEFAULT false');
            return;
        }

        if ($driver === 'mysql') {
            DB::statement('ALTER TABLE unit_risk_monitorings MODIFY COLUMN is_revision TINYINT(1) NOT NULL DEFAULT 0');
            return;
        }

        throw new \RuntimeException("Driver database '{$driver}' belum didukung untuk rollback migration ini.");
    }
};
