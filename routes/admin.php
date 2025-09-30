<?php

use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SupervisorController;
use Illuminate\Support\Facades\Route;

// Admin routes with authentication and admin role middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students.index'); 
    Route::get('/admin/students/{student}', [StudentController::class, 'edit'])->name('admin.students.edit'); 
    Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store');
    Route::delete('/admin/students/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::put('/admin/students/{student}', [StudentController::class, 'update'])->name('admin.students.update');

    // Admin Supervisors
    Route::get('/admin/supervisors', [SupervisorController::class, 'index'])->name('admin.supervisors.index');
    Route::get('/admin/supervisors/{supervisor}', [SupervisorController::class, 'edit'])->name('admin.supervisors.edit');
    Route::post('/admin/supervisors', [SupervisorController::class, 'store'])->name('admin.supervisors.store');
    Route::delete('/admin/supervisors/{supervisor}', [SupervisorController::class, 'destroy'])->name('admin.supervisors.destroy');
    Route::put('/admin/supervisors/{supervisor}', [SupervisorController::class, 'update'])->name('admin.supervisors.update');

    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
    Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::put('/admin/departments/{department}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::delete('/admin/departments/{department}', [DepartmentController::class, 'destroy'])->name('admin.departments.destroy');

    // Admin Projects
    Route::get('/admin/projects', [ProjectController::class, 'index'])->name('admin.projects.index');
    Route::get('/admin/projects/{project}', [ProjectController::class, 'show'])->name('admin.projects.show');
    Route::post('/admin/projects/{project}/allocate', [ProjectController::class, 'allocateSupervisor'])->name('admin.projects.allocate');
    Route::post('/admin/projects/bulk-allocate', [ProjectController::class, 'bulkAllocate'])->name('admin.projects.bulk-allocate');

    // Admin Configurations
    Route::get('/admin/configs', [ConfigController::class, 'index'])->name('admin.configs.index');
    Route::post('/admin/configs', [ConfigController::class, 'store'])->name('admin.configs.store');
    Route::put('/admin/configs/{config}', [ConfigController::class, 'update'])->name('admin.configs.update');
    Route::delete('/admin/configs/{config}', [ConfigController::class, 'destroy'])->name('admin.configs.destroy');
});