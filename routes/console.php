<?php

use App\Jobs\RefreshRecommendationsJob;
use App\Jobs\WeeklyFeedbackJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Jobs
|--------------------------------------------------------------------------
|
| Here we define all scheduled jobs for the application. These jobs
| handle background tasks like refreshing recommendations and
| generating feedback for students.
|
*/

// Refresh recommendations daily at 2 AM
Schedule::job(new RefreshRecommendationsJob)
    ->dailyAt('02:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('refresh-recommendations');

// Generate weekly feedback every Sunday at 8 AM
Schedule::job(new WeeklyFeedbackJob)
    ->weeklyOn(0, '08:00')
    ->withoutOverlapping()
    ->onOneServer()
    ->name('weekly-feedback');
