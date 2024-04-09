<?php

use App\Console\Commands\Boursorama\Aggregate;
use Illuminate\Support\Facades\Schedule;


// Schedule::command(Aggregate::class, ['--fresh', '-n'])->cron("0 0 * * *");

Schedule::command(Aggregate::class, ['-n'])->cron("*/30 * * * *");


Schedule::command(Aggregate::class, ['-n', '--messages', '--ms="OVHCLOUD"'])->cron("*/0 */2 * * *");


// Schedule::command(Aggregate::class, ['-n', '--ms=OVHCLOUD'])->cron("*/15 * * * *");
