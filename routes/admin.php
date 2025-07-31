<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin routes with authentication and admin role middleware
Route::middleware(['auth', 'verified'])->group(function () {
    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Admin Students
    Route::get('/admin/students', [StudentController::class, 'index'])->name('admin.students');

    // Admin Supervisors
    Route::get('/admin/supervisors', function () {
        return Inertia::render('admin/supervisors/Index');
    });

    // Admin Departments
    Route::get('/admin/departments', function () {
        return Inertia::render('admin/departments/Index');
    });

    // Admin Projects
    Route::get('/admin/projects', function () {
        return Inertia::render('admin/projects/Index');
    });
});