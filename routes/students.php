<?php

use App\Http\Controllers\Student\DashboardController;
use App\Http\Controllers\Student\ProjectController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Students Dashboard
Route::get('/students/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');
Route::get('/students', [DashboardController::class, 'index']);

// Student Projects
Route::post('/students/projects', [ProjectController::class, 'store'])->name('student.projects.store');