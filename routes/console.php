<?php

use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Automated Daily Database Backup at Midnight
Schedule::command('db:backup')->dailyAt('00:00');
Schedule::command('db:backup')->twiceDaily(12, 0);
