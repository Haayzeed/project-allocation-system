<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes with authentication and admin role middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students.index'); 
    Route::get('/admin/students/{student}', [StudentController::class, 'edit'])->name('admin.students.edit'); 
    Route::post('/admin/students', [StudentController::class, 'store'])->name('admin.students.store');
    Route::delete('/admin/students/{student}', [StudentController::class, 'destroy'])->name('admin.students.destroy');
    Route::put('/admin/students/{student}', [StudentController::class, 'update'])->name('admin.students.update');

    Route::get('/admin/supervisors', function () {
        return Inertia::render('admin/supervisors/Index');
    });

    Route::get('/admin/departments', [DepartmentController::class, 'index'])->name('admin.departments.index');
    Route::post('/admin/departments', [DepartmentController::class, 'store'])->name('admin.departments.store');
    Route::put('/admin/departments/{department}', [DepartmentController::class, 'update'])->name('admin.departments.update');
    Route::delete('/admin/departments/{department}', [DepartmentController::class, 'destroy'])->name('admin.departments.destroy');

    Route::get('/admin/projects', function () {
        return Inertia::render('admin/projects/Index');
    });
});