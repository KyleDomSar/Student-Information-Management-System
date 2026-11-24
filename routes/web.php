<?php

use App\\Http\\Controllers\\Admin\\DashboardController as AdminDashboardController;
use App\\Http\\Controllers\\Admin\\EnrollmentController as AdminEnrollmentController;
use App\\Http\\Controllers\\Admin\\UserController as AdminUserController;
use App\\Http\\Controllers\\Admin\\AuditLogController;
use App\\Http\\Controllers\\Admin\\ProgramController;
use App\\Http\\Controllers\\Admin\\StudentController;
use App\\Http\\Controllers\\Admin\\StudentReportController;
use App\\Http\\Controllers\\Admin\\YearLevelController;
use App\\Http\\Controllers\\Admin\\SectionController;
use App\\Http\\Controllers\\Auth\\AdminLoginController;
use App\\Http\\Controllers\\Auth\\AuthenticatedSessionController;
use App\\Http\\Controllers\\Auth\\PortalLoginController;
use App\\Http\\Controllers\\Auth\\StudentLoginController;
use App\\Http\\Controllers\\Student\\DashboardController as StudentDashboardController;
use App\\Http\\Controllers\\Student\\EnrollmentController as StudentEnrollmentController;
use App\\Http\\Controllers\\Student\\ProfileController;
use App\\Http\\Controllers\\Student\\VisitorTimeoutController;
use Illuminate\\Support\\Facades\\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->isStudent()
        ? redirect()->route('student.dashboard')
        : redirect()->route('admin.dashboard');
});

Route::get('/login', PortalLoginController::class)->name('login');

Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('admin.login.store');

Route::get('/student/login', [StudentLoginController::class, 'create'])->name('student.login');
Route::post('/student/login', [StudentLoginController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('student.login.store');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin,staff'])
    ->group(function () {
        Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');

        Route::middleware('role:admin,staff')->group(function () {
            Route::resource('students', StudentController::class)->except(['destroy']);
            Route::get('/programs', [ProgramController::class, 'index'])->name('programs.index');
            Route::get('/year-levels', [YearLevelController::class, 'index'])->name('year-levels.index');
            Route::get('/sections', [SectionController::class, 'index'])->name('sections.index');
            Route::get('/reports/students', [StudentReportController::class, 'index'])->name('reports.students');
            Route::get('/reports/students/export', [StudentReportController::class, 'export'])->name('reports.students.export');
            Route::get('/enrollments', [AdminEnrollmentController::class, 'index'])->name('enrollments.index');
            Route::get('/enrollments/create', [AdminEnrollmentController::class, 'create'])->name('enrollments.create');
            Route::post('/enrollments', [AdminEnrollmentController::class, 'store'])->name('enrollments.store');
            Route::get('/enrollments/{enrollment}', [AdminEnrollmentController::class, 'show'])->name('enrollments.show');
        });

        Route::middleware('role:admin')->group(function () {
            Route::patch('/students/{student}/account-status', [StudentController::class, 'updateAccountStatus'])->name('students.account-status');
            Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::resource('programs', ProgramController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
            Route::resource('year-levels', YearLevelController::class)->except(['index', 'show']);
            Route::resource('sections', SectionController::class)->except(['index', 'show']);
            Route::resource('users', AdminUserController::class)
                ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student'])
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/profile', ProfileController::class)->name('profile');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
        Route::get('/visitor-timeouts', [VisitorTimeoutController::class, 'index'])->name('visitor-timeouts.index');
        Route::post('/visitor-timeouts', [VisitorTimeoutController::class, 'store'])
            ->middleware('throttle:5,1')
            ->name('visitor-timeouts.store');
        Route::get('/enrollments', [StudentEnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('/enrollments/{enrollment}', [StudentEnrollmentController::class, 'show'])->name('enrollments.show');
    });
