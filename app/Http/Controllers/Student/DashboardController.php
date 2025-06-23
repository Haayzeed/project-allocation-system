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
        $student = Auth::user()->student;
        
        $student->load([
            'department',
            'projects.specializations',
            'allocation.supervisor.user',
            'allocation.project'
        ]);

        $stats = [
            'total_projects' => $student->projects()->count(),
            'submitted_projects' => $student->projects()->where('status', 'submitted')->count(),
            'approved_projects' => $student->projects()->where('status', 'approved')->count(),
            'has_allocation' => $student->allocation && $student->allocation->status === 'approved',
        ];

        return Inertia::render('Student/Dashboard', [
            'student' => $student,
            'stats' => $stats,
        ]);
    }
} 