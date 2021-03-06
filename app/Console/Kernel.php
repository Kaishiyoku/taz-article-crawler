<?php

namespace App\Console;

use App\Console\Commands\FetchNewArticles;
use App\Console\Commands\FetchNewArticleTitleActivities;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Lumen\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        \Laravelista\LumenVendorPublish\VendorPublishCommand::class,
        FetchNewArticles::class,
        FetchNewArticleTitleActivities::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(FetchNewArticles::class)->everyTenMinutes();
        $schedule->command(FetchNewArticleTitleActivities::class)->everyFiveMinutes();
    }
}
