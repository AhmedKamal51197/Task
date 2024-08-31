<?php

namespace App\Console;

use App\Jobs\DeleteOldSoftDeletedPost;
use App\Jobs\LogUserData;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        //test schedules 
        // $schedule->job(new DeleteOldSoftDeletedPost)->everyMinute();
        // $schedule->job(new LogUserData)->everyMinute();
        $schedule->job(new DeleteOldSoftDeletedPost)->daily();
        $schedule->job(new LogUserData)->everySixHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
