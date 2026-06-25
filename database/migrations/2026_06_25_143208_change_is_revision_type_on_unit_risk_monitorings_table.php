<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Hapus default value sementara
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision DROP DEFAULT');
        
        // 2. Ubah tipe data menjadi SMALLINT (PostgreSQL tidak punya TINYINT)
        // Dan instruksikan cara konversinya: jika true jadi 1, jika false jadi 0
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision TYPE SMALLINT USING (CASE WHEN is_revision=true THEN 1 ELSE 0 END)');
        
        // 3. Set kembali default value-nya menjadi angka 0
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision SET DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // 1. Hapus default value
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision DROP DEFAULT');
        
        // 2. Kembalikan ke boolean. Jika angkanya > 0 kembalikan jadi true, sisanya false.
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision TYPE BOOLEAN USING (CASE WHEN is_revision > 0 THEN true ELSE false END)');
        
        // 3. Set default kembali ke false
        DB::statement('ALTER TABLE unit_risk_monitorings ALTER COLUMN is_revision SET DEFAULT false');
    }
};