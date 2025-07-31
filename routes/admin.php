<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Admin Dashboard
Route::get('/admin/dashboard', function () {
    return Inertia::render('admin/dashboard/Index');
});

// Admin Students
Route::get('/admin/students', function () {
    return Inertia::render('admin/students/Index');
});

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