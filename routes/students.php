<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/students/login', function () {
    return Inertia::render('students/Login');
});

Route::get('/students', function () {
    return Inertia::render('students/Index');
}); 