<?php

namespace App\Console;

use App\Console\Commands\CarReminder;
use App\Console\Commands\ClearActiveUsers;
use App\Console\Commands\CreateCreditSalesGoods;
use App\Console\Commands\CreateLodgementCosts;
use App\Console\Commands\CreateLodgements;
use App\Console\Commands\CreateVendingSalesGoods;
use App\Console\Commands\DatabaseDump;
use App\Console\Commands\DevTool;
use App\Console\Commands\GetExchangeRates;
use App\Console\Commands\HistoryExchangeRates;
use App\Console\Commands\PhasedBudgetReminder;
use App\Console\Commands\ToggleRowsVisibility;
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
        DatabaseDump::class,
        CarReminder::class,
        PhasedBudgetReminder::class,
        ClearActiveUsers::class,
        GetExchangeRates::class,
        HistoryExchangeRates::class,
        CreateLodgementCosts::class,
        ToggleRowsVisibility::class,
        DevTool::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
