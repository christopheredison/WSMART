<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Tutup risiko unit yang sudah tidak valid setiap hari pukul 01:00
        $schedule->command('risks:close-expired-units')->dailyAt('01:00');

        // Sinkronisasi data project WIKA setiap hari pukul 02:00
        $schedule->command('projects:sync-wika')->dailyAt('02:00');

        // Sinkronisasi hasil usaha / LSP proyek setiap hari pukul 02:30
        $schedule->command('projects:sync-hasil-usaha')->dailyAt('02:30');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
