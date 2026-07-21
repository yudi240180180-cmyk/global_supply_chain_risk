<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── Automatic Data Sync Schedule ─────────────────────────────────────────
// Sync exchange rates every 6 hours (rates change frequently)
Schedule::command('sync:rates')->everySixHours();

// Sync news every 3 hours
Schedule::command('sync:news')->everyThreeHours();

// Sync weather every 6 hours
Schedule::command('sync:weather')->everySixHours();

// Sync economics once a week (World Bank data doesn't change often)
Schedule::command('sync:economics')->weekly()->sundays()->at('02:00');

// Sync countries once a week
Schedule::command('sync:countries')->weekly()->sundays()->at('01:00');

// Calculate risk scores every 12 hours
Schedule::command('calculate:risk-scores')->everyTwelveHours();

// Analyze sentiment every 6 hours
Schedule::command('analyze:sentiment')->everySixHours();
