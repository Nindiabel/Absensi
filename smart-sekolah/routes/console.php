<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Carbon\Carbon;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Run generate-alpha every minute. The command itself has logic to only generate
// when current time passes the limit.
Schedule::command('absensi:generate-alpha')
    ->everyMinute()
    ->timezone('Asia/Jakarta');
