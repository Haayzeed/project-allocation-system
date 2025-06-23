<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the supervisor dashboard.
     */
    public function index(): Response
    {
        $supervisor = Auth::user()->supervisor;
        
        $supervisor->load([
            'department',
            'specializations',
            'allocations.student.user',
            'allocations.project.specializations'
        ]);

        $stats = [
            'total_allocations' => $supervisor->allocations()->count(),
            'approved_allocations' => $supervisor->allocations()->where('status', 'approved')->count(),
            'pending_allocations' => $supervisor->allocations()->where('status', 'pending')->count(),
            'current_student_count' => $supervisor->current_student_count,
            'max_students' => $supervisor->max_students,
        ];

        return Inertia::render('Supervisor/Dashboard', [
            'supervisor' => $supervisor,
            'stats' => $stats,
        ]);
    }
} 