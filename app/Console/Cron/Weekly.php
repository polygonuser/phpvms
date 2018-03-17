<?php

namespace App\Console\Cron;

use App\Console\BaseCommand;
use App\Events\CronMonthly;

/**
 * This just calls the CronWeekly event, so all of the
 * listeners, etc can just be called to run those tasks
 * @package App\Console\Cron
 */
class Weekly extends BaseCommand
{
    protected $signature = 'cron:monthly';
    protected $description = 'Run the monthly cron tasks';
    protected $schedule;

    public function handle(): void
    {
        $this->redirectLoggingToStdout();
        event(new CronMonthly());
    }
}
