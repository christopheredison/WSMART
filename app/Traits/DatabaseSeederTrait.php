<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DatabaseSeederTrait
{
    protected function disableForeignKeyChecks()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        } else {
            DB::statement('SET CONSTRAINTS ALL DEFERRED');
        }
    }

    protected function enableForeignKeyChecks()
    {
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        } else {
            DB::statement('SET CONSTRAINTS ALL IMMEDIATE');
        }
    }
} 