<?php

use App\Http\Controllers\Admin\ClassController as AdminClassController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\FeedbackController;
use App\Http\Controllers\Student\LearningProfileController;
use App\Http\Controllers\Student\MaterialController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\ProgressController;
use App\Http\Controllers\Student\QuestionnaireController;
use App\Http\Controllers\Student\RecommendationController;
use App\Http\Controllers\Student\SubjectEnrollmentController;
use App\Http\Controllers\Student\SubjectLearningController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\MaterialController as TeacherMaterialController;
use App\Http\Controllers\Teacher\StudentController as TeacherStudentController;
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

    Route::get('/recommendations', [RecommendationController::class, 'index'])->name('recommendations.index');
    Route::post('/recommendations/refresh', [RecommendationController::class, 'refresh'])->name('recommendations.refresh');
    Route::post('/recommendations/generate', [RecommendationController::class, 'generate'])->name('recommendations.generate');
    Route::post('/recommendations/{recommendation}/view', [RecommendationController::class, 'markViewed'])->name('recommendations.view');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::get('/feedback/{feedback}', [FeedbackController::class, 'show'])->name('feedback.show');
    Route::post('/feedback/generate', [FeedbackController::class, 'generate'])->name('feedback.generate');

    Route::get('/progress', [ProgressController::class, 'index'])->name('progress.index');

    // Subject Enrollment
    Route::get('/subjects', [SubjectEnrollmentController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/available', [SubjectEnrollmentController::class, 'available'])->name('subjects.available');
    Route::post('/subjects/{subject}/enroll', [SubjectEnrollmentController::class, 'enroll'])->name('subjects.enroll');
    Route::delete('/subjects/{subject}/unenroll', [SubjectEnrollmentController::class, 'unenroll'])->name('subjects.unenroll');

    // Subject Learning
    Route::get('/subjects/{subject}/learn', [SubjectLearningController::class, 'show'])->name('subjects.learn');
    Route::get('/subjects/{subject}/topics', [SubjectLearningController::class, 'topics'])->name('subjects.topics');
    Route::get('/subjects/{subject}/topics/{topic}', [SubjectLearningController::class, 'topic'])->name('subjects.topic');
    Route::post('/subjects/{subject}/topics/{topic}/start', [SubjectLearningController::class, 'startTopic'])->name('subjects.topic.start');
    Route::post('/subjects/{subject}/topics/{topic}/complete', [SubjectLearningController::class, 'completeTopic'])->name('subjects.topic.complete');
});

// Teacher Routes
Route::middleware(['auth', 'verified', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

    // Materials CRUD
    Route::get('/materials', [TeacherMaterialController::class, 'index'])->name('materials.index');
    Route::get('/materials/create', [TeacherMaterialController::class, 'create'])->name('materials.create');
    Route::post('/materials', [TeacherMaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}', [TeacherMaterialController::class, 'show'])->name('materials.show');
    Route::get('/materials/{material}/edit', [TeacherMaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [TeacherMaterialController::class, 'update'])->name('materials.update');
    Route::delete('/materials/{material}', [TeacherMaterialController::class, 'destroy'])->name('materials.destroy');
    Route::patch('/materials/{material}/toggle-active', [TeacherMaterialController::class, 'toggleActive'])->name('materials.toggle-active');

    // Students
    Route::get('/students', [TeacherStudentController::class, 'index'])->name('students.index');
    Route::get('/students/{student}', [TeacherStudentController::class, 'show'])->name('students.show');
});

// Admin Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Users CRUD
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Classes CRUD
    Route::get('/classes', [AdminClassController::class, 'index'])->name('classes.index');
    Route::get('/classes/create', [AdminClassController::class, 'create'])->name('classes.create');
    Route::post('/classes', [AdminClassController::class, 'store'])->name('classes.store');
    Route::get('/classes/{class}', [AdminClassController::class, 'show'])->name('classes.show');
    Route::get('/classes/{class}/edit', [AdminClassController::class, 'edit'])->name('classes.edit');
    Route::put('/classes/{class}', [AdminClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{class}', [AdminClassController::class, 'destroy'])->name('classes.destroy');
    Route::patch('/classes/{class}/toggle-active', [AdminClassController::class, 'toggleActive'])->name('classes.toggle-active');

    // Subjects CRUD
    Route::get('/subjects', [AdminSubjectController::class, 'index'])->name('subjects.index');
    Route::get('/subjects/create', [AdminSubjectController::class, 'create'])->name('subjects.create');
    Route::post('/subjects', [AdminSubjectController::class, 'store'])->name('subjects.store');
    Route::get('/subjects/{subject}', [AdminSubjectController::class, 'show'])->name('subjects.show');
    Route::get('/subjects/{subject}/edit', [AdminSubjectController::class, 'edit'])->name('subjects.edit');
    Route::put('/subjects/{subject}', [AdminSubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [AdminSubjectController::class, 'destroy'])->name('subjects.destroy');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::post('/reports/generate', [AdminReportController::class, 'generate'])->name('reports.generate');
});

require __DIR__.'/settings.php';
