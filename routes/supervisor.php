<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Supervisor Login
Route::get('/supervisor/login', function () {
    return Inertia::render('supervisor/Login');
});

// Supervisor Dashboard
Route::get('/supervisor', function () {
    return Inertia::render('supervisor/Index');
});

// Supervisor Students
Route::get('/supervisor/students', function () {
    return Inertia::render('supervisor/students/Index');
}); 