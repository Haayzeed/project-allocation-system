<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Config;
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
        // dd('here');
        $supervisor = Auth::user()->supervisor;
        
        $supervisor->load([
            'department',
            'specializations',
            'allocations.student.user',
            'allocations.project.specializations'
        ]);

        $maxStudentsPerSupervisor = Config::getValue('max_students_per_supervisor', 8);
        
        $stats = [
            'total_allocations' => $supervisor->allocations()->count(),
            'approved_allocations' => $supervisor->allocations()->where('status', 'approved')->count(),
            'pending_allocations' => $supervisor->allocations()->where('status', 'pending')->count(),
            'current_student_count' => $supervisor->current_student_count,
            'max_students' => $maxStudentsPerSupervisor,
            
        ];

        return Inertia::render('supervisor/Index', [
            'supervisor' => $supervisor,
            'stats' => $stats,
            'total_students' => $supervisor->allocations()->count(),
        ]);
    }
} 