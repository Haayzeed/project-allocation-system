<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\StudentController;
use App\Models\Student;
use App\Models\Project;
use App\Models\Department;
use App\Models\Supervisor;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Route::get('/student', function () {
//     return Inertia::render('Student');
// })->name('student');

Route::get('dashboard', function () {
    $stats = [
        'total_students' => Student::count(),
        'total_projects' => Project::count(),
        'total_departments' => Department::count(),
        'total_supervisors' => Supervisor::count(),
    ];
    return Inertia::render('Dashboard', [
        'stats' => $stats,
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/students', function () {
    return Inertia::render('student/Index');
});

Route::get('/departments', function () {
    return Inertia::render('department/Index');
});

Route::get('/supervisors', function () {
    return Inertia::render('supervisor/Index');
});

Route::get('/projects', function () {
    return Inertia::render('projects/Index');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
