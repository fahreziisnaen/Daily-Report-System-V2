<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\ReportController;
use App\Models\User;
use App\Models\Report;
use Illuminate\Http\Request;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\HrReviewController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Admin routes
    Route::group(['middleware' => ['auth', 'can:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
        Route::get('/rekap/export/{user}', [RekapController::class, 'export'])->name('rekap.export');
        Route::resource('projects', ProjectController::class);
        Route::get('/api/active-projects', [ProjectController::class, 'getActiveProjects']);
        Route::put('/users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
        
        // Department routes
        Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
        Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy');
    });

    // Employee routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
    Route::get('/reports/{report}/edit', [ReportController::class, 'edit'])->name('reports.edit');
    Route::put('/reports/{report}', [ReportController::class, 'update'])->name('reports.update');
    Route::delete('/reports/{report}', [ReportController::class, 'destroy'])->name('reports.destroy');
    
    // Reports additional routes
    Route::get('/reports/{report}/export', [ReportController::class, 'export'])->name('reports.export');
    Route::post('/reports/{report}/submit', [ReportController::class, 'submit'])->name('reports.submit');
    Route::post('/reports/{report}/resubmit', [ReportController::class, 'resubmit'])->name('reports.resubmit');
    Route::post('/reports/{report}/resubmit-vp', [ReportController::class, 'resubmitVp'])->name('reports.resubmit-vp');

    // Employee rekap routes
    Route::get('/rekap', [RekapController::class, 'employeeRekap'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'employeeExport'])->name('rekap.employee.export');
    Route::get('/rekap/export/{user}', [RekapController::class, 'export'])->name('rekap.export');

    // HR Rekap Route
    Route::get('/hr-rekap', [RekapController::class, 'hrRekap'])->name('hr.rekap');

    // API routes for search
    Route::middleware('auth')->group(function () {
        Route::get('/api/search-employees', function (Request $request) {
            return User::role('employee')
                ->where('name', 'LIKE', '%' . $request->q . '%')
                ->pluck('name');
        });

        Route::get('/api/search-locations', function (Request $request) {
            return Report::where('location', 'LIKE', '%' . $request->q . '%')
                ->distinct()
                ->pluck('location');
        });

        Route::get('/api/search-projects', function (Request $request) {
            return Report::where('project_code', 'LIKE', '%' . $request->q . '%')
                ->distinct()
                ->pluck('project_code');
        });

        // Tambahkan route baru untuk mengambil project aktif
        Route::get('/api/active-projects', [ProjectController::class, 'getActiveProjects']);
    });

    // Get Vice Presidents based on Verifikator
    Route::get('/vice-presidents', [ReportController::class, 'getVicePresidents'])->name('get.vice.presidents');

    // Approval
    Route::get('/reports/{report}/approve', [ReportController::class, 'approveView'])->name('reports.approve.view');
    Route::post('/reports/{report}/approve', [ReportController::class, 'approve'])->name('reports.approve');
    Route::post('/reports/{report}/reject', [ReportController::class, 'reject'])->name('reports.reject');

    // Verification routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/verification', [VerificationController::class, 'index'])
            ->name('verification.index');
        Route::get('/verification/{report}', [VerificationController::class, 'show'])
            ->name('verification.show');
        Route::post('/verification/{report}/approve', [VerificationController::class, 'approve'])
            ->name('verification.approve');
        Route::post('/verification/{report}/reject', [VerificationController::class, 'reject'])
            ->name('verification.reject');
    });

    // VP Approval Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/approval', [ApprovalController::class, 'index'])
            ->name('approval.index');
        Route::get('/approval/{report}', [ApprovalController::class, 'show'])
            ->name('approval.show');
        Route::post('/approval/{report}/approve', [ApprovalController::class, 'approve'])
            ->name('approval.approve');
        Route::post('/approval/{report}/reject', [ApprovalController::class, 'reject'])
            ->name('approval.reject');
    });

    // HR Review Routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/hr-review', [HrReviewController::class, 'index'])
            ->name('hr-review.index');
        Route::get('/hr-review/{report}', [HrReviewController::class, 'show'])
            ->name('hr-review.show');
        Route::post('/hr-review/{report}/approve', [HrReviewController::class, 'approve'])
            ->name('hr-review.approve');
        Route::post('/hr-review/{report}/reject', [HrReviewController::class, 'reject'])
            ->name('hr-review.reject');
    });
});

require __DIR__.'/auth.php';
