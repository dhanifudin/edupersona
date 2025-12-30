<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\LearningProfileController;
use App\Http\Controllers\Student\MaterialController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\QuestionnaireController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Student Routes
Route::middleware(['auth', 'verified', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/questionnaire', [QuestionnaireController::class, 'index'])->name('questionnaire.index');
    Route::post('/questionnaire', [QuestionnaireController::class, 'store'])->name('questionnaire.store');

    Route::get('/learning-profile', [LearningProfileController::class, 'show'])->name('learning-profile.show');

    Route::get('/materials', [MaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
    Route::patch('/activities/{activity}', [MaterialController::class, 'updateActivity'])->name('activities.update');
    Route::post('/recommendations/refresh', [MaterialController::class, 'refreshRecommendations'])->name('recommendations.refresh');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/feedback/generate', [FeedbackController::class, 'generate'])->name('feedback.generate');
});

require __DIR__.'/settings.php';
