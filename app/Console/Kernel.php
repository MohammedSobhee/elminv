<?php

namespace App\Console;

use App\Schedule\CleanupSoftDeletes;
use App\Schedule\CleanupUserSessionData;
use App\Schedule\SendSchoolExpiringNotice;
use App\Schedule\DeleteVideoCons;
use App\Schedule\DeleteActivityLogs;
use App\Schedule\DeleteChats;
use App\Schedule\DeleteDemos;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('telescope:prune')->daily();
        $schedule->call(new SendSchoolExpiringNotice)->daily();
        $schedule->call(new DeleteVideoCons)->monthly();
        $schedule->call(new DeleteChats)->monthly();
        $schedule->call(new DeleteActivityLogs)->daily();
        $schedule->call(new CleanupUserSessionData)->weekly();
        $schedule->call(new DeleteDemos)->daily();
        //$schedule->call(new CleanupSoftDeletes)->monthly();
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
