<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteRestore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complete_restore {dbOldDatabase}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Complete restore db';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $dbConfig = config('database.connections.' . config('database.default'));
        $dbDatabase = $dbConfig['database'];
        $dbBackupDatabase = $dbConfig['database'] . '_' . date('YmdHis') . rand(1111,9999);
        $dbOldDatabase = $this->argument('dbOldDatabase');
        DB::connection('pgsql_restore')->statement("SELECT 
            pg_terminate_backend(pid) 
        FROM 
            pg_stat_activity 
        WHERE 
            datname = '$dbDatabase' OR datname = '$dbOldDatabase'
        ;");
        DB::connection('pgsql_restore')->statement("ALTER DATABASE $dbDatabase RENAME TO $dbBackupDatabase");
        DB::connection('pgsql_restore')->statement("ALTER DATABASE $dbOldDatabase RENAME TO $dbDatabase");
        $this->info('Success restore backup');
    }
}
