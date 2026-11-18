<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\PortalLoginController;
use App\Http\Controllers\Auth\StudentLoginController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\ProfileController;
use Illuminate\Support\Facades\Route;

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

        // Student and program records are restricted to Admin and Registrar/Staff.
        Route::middleware('role:admin,staff')->group(function () {
            Route::resource('students', StudentController::class);
            Route::resource('programs', ProgramController::class)
                ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
            Route::resource('subjects', SubjectController::class)
                ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        });
    });

Route::prefix('student')
    ->name('student.')
    ->middleware(['auth', 'role:student'])
    ->group(function () {
        Route::get('/dashboard', StudentDashboardController::class)->name('dashboard');
        Route::get('/profile', ProfileController::class)->name('profile');
    });
