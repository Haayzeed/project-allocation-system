<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AllocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(
        private AllocationService $allocationService
    ) {}

    public function index(): Response
    {
        $projects = Project::with([
            'student.user:id,name,email',
            'student.department:id,name',
            'allocation.supervisor.user:id,name',
            'specializations:id,name'
        ])
        ->where('status', 'submitted')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'description' => $project->description,
                'objectives' => $project->objectives,
                'methodology' => $project->methodology,
                'status' => $project->status,
                'created_at' => $project->created_at,
                'student' => [
                    'id' => $project->student->id,
                    'name' => $project->student->user->name,
                    'email' => $project->student->user->email,
                    'student_id' => $project->student->student_id,
                    'level' => $project->student->level,
                    'session' => $project->student->session,
                ],
                'department' => [
                    'id' => $project->student->department->id,
                    'name' => $project->student->department->name,
                ],
                'specializations' => $project->specializations->map(function ($spec) {
                    return [
                        'id' => $spec->id,
                        'name' => $spec->name,
                    ];
                }),
                'allocation' => $project->allocation ? [
                    'id' => $project->allocation->id,
                    'status' => $project->allocation->status,
                    'match_score' => $project->allocation->match_score,
                    'admin_notes' => $project->allocation->admin_notes,
                    'supervisor' => [
                        'id' => $project->allocation->supervisor->id,
                        'name' => $project->allocation->supervisor->user->name,
                    ],
                ] : null,
                'allocation_status' => $project->allocation ? 
                    ucfirst($project->allocation->status) : 'Not Allocated',
            ];
        });

        return Inertia::render('admin/projects/Index', [
            'projects' => $projects,
        ]);
    }

    public function allocateSupervisor(Request $request, Project $project)
    {
        try {
            // Check if project is already allocated
            if ($project->allocation && $project->allocation->status !== 'rejected') {
                return back()->with('error', 'Project is already allocated or pending allocation.');
            }

            // Check if project is submitted
            if ($project->status !== 'submitted') {
                return back()->with('error', 'Only submitted projects can be allocated.');
            }

            // Use the allocation service to find and assign best supervisor
            $allocation = $this->allocationService->allocateProject($project);

            if ($allocation) {
                Log::info('AI Allocation created', [
                    'project_id' => $project->id,
                    'student_id' => $project->student_id,
                    'supervisor_id' => $allocation->supervisor_id,
                    'match_score' => $allocation->match_score,
                ]);

                return back()->with('success', 
                    "Project '{$project->title}' has been allocated to a supervisor with a match score of " . 
                    round($allocation->match_score, 1) . "%. Status: Pending approval."
                );
            } else {
                return back()->with('error', 'Failed to allocate supervisor. No suitable matches found.');
            }

        } catch (\Exception $e) {
            Log::error('Project allocation failed', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Allocation failed: ' . $e->getMessage());
        }
    }

    public function show(Project $project): Response
    {
        $project->load([
            'student.user',
            'student.department',
            'allocation.supervisor.user',
            'specializations'
        ]);

        return Inertia::render('admin/projects/Show', [
            'project' => $project,
        ]);
    }

    public function bulkAllocate(Request $request)
    {
        try {
            // Get all submitted projects without allocations
            $unallocatedProjects = Project::with(['student'])
                ->where('status', 'submitted')
                ->whereDoesntHave('allocation')
                ->get();

            if ($unallocatedProjects->isEmpty()) {
                return back()->with('info', 'No unallocated submitted projects found.');
            }

            $results = $this->allocationService->generateAllocations();

            $allocatedCount = count($results['allocations']);
            $errorCount = count($results['errors']);

            $message = "Bulk allocation completed. {$allocatedCount} projects allocated successfully.";
            if ($errorCount > 0) {
                $message .= " {$errorCount} errors occurred.";
            }

            return back()->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Bulk allocation failed', [
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Bulk allocation failed: ' . $e->getMessage());
        }
    }
}
