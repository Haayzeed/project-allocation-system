<?php

use App\Http\Controllers\Supervisor\DashboardController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Supervisor Login
Route::get('/supervisor/dashboard', [DashboardController::class, 'index'])->name('supervisor.dashboard');

// Supervisor Dashboard
Route::get('/supervisor', function () {
    return Inertia::render('supervisor/Index');
});

// Supervisor Students
Route::get('/supervisor/students', function () {
    return Inertia::render('supervisor/students/Index');
}); 