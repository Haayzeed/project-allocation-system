<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Department;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalSupervisors = User::where('role', 'supervisor')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        $totalProjects = Project::count();
        $totalAllocations = Allocation::count();
        $totalDepartments = Department::count();

        $projectStats = Project::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $allocationStats = Allocation::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $recentAllocations = Allocation::with([
            'student.user:id,name',
            'supervisor.user:id,name',
            'project:id,title'
        ])
            ->latest()
            ->limit(5)
            ->get();

        $recentProjects = Project::with([
            'student.user:id,name'
        ])
            ->latest()
            ->limit(5)
            ->get();

        $recentUsers = User::latest()
            ->limit(5)
            ->get(['id', 'name', 'email', 'role', 'created_at']);

        $departmentStats = Department::withCount(['students', 'supervisors'])
            ->get();

        $supervisorWorkload = Supervisor::with(['user:id,name'])
            ->withCount(['allocations' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->get()
            ->map(function ($supervisor) {
                return [
                    'id' => $supervisor->id,
                    'name' => $supervisor->user->name,
                    'current_students' => $supervisor->allocations_count,
                    'max_students' => $supervisor->max_students,
                    'percentage_filled' => $supervisor->max_students > 0 
                        ? round(($supervisor->allocations_count / $supervisor->max_students) * 100, 1)
                        : 0,
                    'can_accept_more' => $supervisor->allocations_count < $supervisor->max_students,
                ];
            });

        $approvedAllocations = Allocation::where('status', 'approved')->count();
        $allocationSuccessRate = $totalAllocations > 0 
            ? round(($approvedAllocations / $totalAllocations) * 100, 1)
            : 0;

        $averageMatchScore = Allocation::where('status', 'approved')
            ->whereNotNull('match_score')
            ->avg('match_score');

        $monthlyAllocations = Allocation::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $stats = [
            'total_users' => $totalUsers,
            'total_students' => $totalStudents,
            'total_supervisors' => $totalSupervisors,
            'total_admins' => $totalAdmins,
            'total_projects' => $totalProjects,
            'total_allocations' => $totalAllocations,
            'total_departments' => $totalDepartments,
            'allocation_success_rate' => $allocationSuccessRate,
            'average_match_score' => $averageMatchScore ? round($averageMatchScore, 2) : null,
        ];

        return Inertia::render('admin/dashboard/Index', [
            'stats' => $stats,
            'projectStats' => $projectStats,
            'allocationStats' => $allocationStats,
            'recentAllocations' => $recentAllocations,
            'recentProjects' => $recentProjects,
            'recentUsers' => $recentUsers,
            'departmentStats' => $departmentStats,
            'supervisorWorkload' => $supervisorWorkload,
            'monthlyAllocations' => $monthlyAllocations,
        ]);
    }
} 