<?php

use App\Models\Group;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Watchdog Daemon (SDD 5.2 Moderation and Inactivity Management Module).
 * Runs daily: scans every group for members past their inactivity_warning_period,
 * issues warnings, and auto-blacklists members with two unresolved warnings.
 */
Schedule::call(function () {
    $controller = app(\App\Http\Controllers\Api\ModerationController::class);
    Group::all()->each(fn (Group $group) => $controller->scanInactivity($group));
})->daily()->name('inactivity-watchdog');


Schedule::command('quizzes:open-scheduled')->everyMinute();
Schedule::command('quizzes:close-expired')->everyMinute();
