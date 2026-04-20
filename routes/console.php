<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('comments:anonymize-ips')->dailyAt('03:15');
Schedule::command('health:queue-check-heartbeat')->everyMinute();
Schedule::command('health:schedule-check-heartbeat')->everyMinute();
