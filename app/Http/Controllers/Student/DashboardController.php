<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the student dashboard.
     */
    public function index(): Response
    {
        $user = Auth::user();
        
        $user->load([
            'student.department',
            'student.projects.specializations',
            'student.allocation.supervisor.user',
            'student.allocation.project'
        ]);

        return Inertia::render('students/Index', [
            'user' => $user,
        ]);
    }
} 