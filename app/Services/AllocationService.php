<?php

namespace App\Services;

use App\Models\Allocation;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AllocationService
{
    protected LLMService $llmService;

    public function __construct(LLMService $llmService = null)
    {
        $this->llmService = $llmService ?? LLMFactory::create();
    }

    /**
     * Generate AI-based allocations for all submitted projects using LLM.
     */
    public function generateAllocations(): array
    {
        $submittedProjects = Project::with(['student', 'specializations'])
            ->where('status', 'submitted')
            ->whereDoesntHave('allocation')
            ->get();

        if ($submittedProjects->isEmpty()) {
            return [
                'allocations' => [],
                'errors' => ['No submitted projects found for allocation.'],
            ];
        }

        try {
            // Prepare data for LLM
            $students = $this->prepareStudentsData($submittedProjects);
            $projects = $this->prepareProjectsData($submittedProjects);
            $supervisors = $this->prepareSupervisorsData();

            // Get LLM recommendations
            $llmRecommendations = $this->llmService->generateAllocationRecommendations(
                $students,
                $projects,
                $supervisors
            );

            if (!empty($llmRecommendations['errors'])) {
                return [
                    'allocations' => [],
                    'errors' => $llmRecommendations['errors'],
                ];
            }

            // Process LLM recommendations and create allocations
            $allocations = $this->processLLMRecommendations($llmRecommendations['allocations']);

            return [
                'allocations' => $allocations,
                'summary' => $llmRecommendations['summary'] ?? [],
                'recommendations' => $llmRecommendations['recommendations'] ?? [],
                'errors' => [],
            ];

        } catch (\Exception $e) {
            Log::error('LLM allocation generation failed', [
                'error' => $e->getMessage(),
                'projects_count' => $submittedProjects->count(),
            ]);

            return [
                'allocations' => [],
                'errors' => ['LLM allocation failed: ' . $e->getMessage()],
            ];
        }
    }

    /**
     * Prepare students data for LLM consumption.
     */
    private function prepareStudentsData(Collection $projects): array
    {
        $studentIds = $projects->pluck('student_id')->unique();
        $students = Student::with(['user', 'department'])
            ->whereIn('id', $studentIds)
            ->get();

        return $students->map(function ($student) {
            return [
                'id' => $student->id,
                'name' => $student->user->name,
                'email' => $student->user->email,
                'student_id' => $student->student_id,
                'department' => [
                    'id' => $student->department->id,
                    'name' => $student->department->name,
                    'code' => $student->department->code,
                ],
                'level' => $student->level,
                'session' => $student->session,
            ];
        })->toArray();
    }

    /**
     * Prepare projects data for LLM consumption.
     */
    private function prepareProjectsData(Collection $projects): array
    {
        return $projects->map(function ($project) {
            return [
                'id' => $project->id,
                'student_id' => $project->student_id,
                'title' => $project->title,
                'description' => $project->description,
                'objectives' => $project->objectives,
                'methodology' => $project->methodology,
                'status' => $project->status,
                'specializations' => $project->specializations->map(function ($specialization) {
                    return [
                        'id' => $specialization->id,
                        'name' => $specialization->name,
                        'description' => $specialization->description,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Prepare supervisors data for LLM consumption.
     */
    private function prepareSupervisorsData(): array
    {
        $supervisors = Supervisor::with(['user', 'department', 'specializations', 'allocations'])
            ->where('is_active', true)
            ->get();

        return $supervisors->map(function ($supervisor) {
            return [
                'id' => $supervisor->id,
                'name' => $supervisor->user->name,
                'email' => $supervisor->user->email,
                'staff_id' => $supervisor->staff_id,
                'title' => $supervisor->title,
                'bio' => $supervisor->bio,
                'department' => [
                    'id' => $supervisor->department->id,
                    'name' => $supervisor->department->name,
                    'code' => $supervisor->department->code,
                ],
                'max_students' => $supervisor->max_students,
                'current_student_count' => $supervisor->current_student_count,
                'specializations' => $supervisor->specializations->map(function ($specialization) {
                    return [
                        'id' => $specialization->id,
                        'name' => $specialization->name,
                        'description' => $specialization->description,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    /**
     * Process LLM recommendations and create allocations.
     */
    private function processLLMRecommendations(array $recommendations): array
    {
        $allocations = [];
        $errors = [];

        foreach ($recommendations as $recommendation) {
            try {
                $allocation = $this->createAllocationFromRecommendation($recommendation);
                if ($allocation) {
                    $allocations[] = $allocation;
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to process recommendation: " . $e->getMessage();
            }
        }

        if (!empty($errors)) {
            Log::warning('Some LLM recommendations failed to process', ['errors' => $errors]);
        }

        return $allocations;
    }

    /**
     * Create allocation from LLM recommendation.
     */
    private function createAllocationFromRecommendation(array $recommendation): ?Allocation
    {
        // Validate recommendation
        if (!isset($recommendation['student_id'], $recommendation['supervisor_id'], $recommendation['project_id'])) {
            throw new \Exception('Invalid recommendation format');
        }

        $studentId = $recommendation['student_id'];
        $supervisorId = $recommendation['supervisor_id'];
        $projectId = $recommendation['project_id'];
        $matchScore = $recommendation['match_score'] ?? 0;

        // Check if student already has an approved allocation
        $existingAllocation = Allocation::where('student_id', $studentId)
            ->where('status', 'approved')
            ->first();

        if ($existingAllocation) {
            throw new \Exception("Student {$studentId} already has an approved allocation");
        }

        // Check if supervisor can accept more students
        $supervisor = Supervisor::find($supervisorId);
        if (!$supervisor || !$supervisor->canAcceptMoreStudents()) {
            throw new \Exception("Supervisor {$supervisorId} cannot accept more students");
        }

        // Check if project exists and belongs to the student
        $project = Project::where('id', $projectId)
            ->where('student_id', $studentId)
            ->first();

        if (!$project) {
            throw new \Exception("Project {$projectId} not found or doesn't belong to student {$studentId}");
        }

        // Create the allocation
        $allocation = Allocation::create([
            'project_id' => $projectId,
            'supervisor_id' => $supervisorId,
            'student_id' => $studentId,
            'status' => 'pending',
            'match_score' => $matchScore,
            'admin_notes' => $recommendation['reasoning'] ?? 'AI-generated allocation',
        ]);

        return $allocation;
    }

    /**
     * Allocate a specific project to the best matching supervisor (fallback method).
     */
    public function allocateProject(Project $project): ?Allocation
    {
        // Get available supervisors (active and not at capacity)
        $availableSupervisors = Supervisor::with(['specializations', 'allocations'])
            ->where('is_active', true)
            ->whereDoesntHave('allocations', function ($query) {
                $query->where('status', 'approved');
            })
            ->orWhereHas('allocations', function ($query) {
                $query->where('status', 'approved');
            }, '<', DB::raw('max_students'))
            ->get();

        if ($availableSupervisors->isEmpty()) {
            throw new \Exception('No available supervisors found.');
        }

        // Calculate match scores for each supervisor
        $matches = $this->calculateMatchScores($project, $availableSupervisors);

        if ($matches->isEmpty()) {
            throw new \Exception('No suitable supervisors found for this project.');
        }

        // Get the best match
        $bestMatch = $matches->first();

        // Create the allocation
        $allocation = Allocation::create([
            'project_id' => $project->id,
            'supervisor_id' => $bestMatch['supervisor']->id,
            'student_id' => $project->student_id,
            'status' => 'pending',
            'match_score' => $bestMatch['score'],
        ]);

        return $allocation;
    }

    /**
     * Calculate match scores between a project and available supervisors.
     */
    private function calculateMatchScores(Project $project, Collection $supervisors): Collection
    {
        $matches = collect();

        foreach ($supervisors as $supervisor) {
            $score = $this->calculateMatchScore($project, $supervisor);
            
            if ($score > 0) {
                $matches->push([
                    'supervisor' => $supervisor,
                    'score' => $score,
                ]);
            }
        }

        // Sort by score (highest first)
        return $matches->sortByDesc('score')->values();
    }

    /**
     * Calculate a match score between a project and supervisor.
     */
    private function calculateMatchScore(Project $project, Supervisor $supervisor): float
    {
        $score = 0;

        // Check specialization matches
        $projectSpecializations = $project->specializations->pluck('id')->toArray();
        $supervisorSpecializations = $supervisor->specializations->pluck('id')->toArray();

        if (!empty($projectSpecializations) && !empty($supervisorSpecializations)) {
            $matchingSpecializations = array_intersect($projectSpecializations, $supervisorSpecializations);
            $specializationScore = (count($matchingSpecializations) / count($projectSpecializations)) * 100;
            $score += $specializationScore * 0.7; // 70% weight for specialization match
        }

        // Check department match (bonus points for same department)
        if ($project->student->department_id === $supervisor->department_id) {
            $score += 20; // 20% bonus for same department
        }

        // Check supervisor capacity (prefer supervisors with more capacity)
        $currentLoad = $supervisor->current_student_count;
        $maxCapacity = $supervisor->max_students;
        $capacityScore = (($maxCapacity - $currentLoad) / $maxCapacity) * 10;
        $score += $capacityScore; // Up to 10% bonus for capacity

        // Ensure score doesn't exceed 100
        return min($score, 100);
    }

    /**
     * Reallocate a project to a different supervisor.
     */
    public function reallocateProject(Allocation $allocation, Supervisor $newSupervisor): Allocation
    {
        // Check if new supervisor can accept more students
        if (!$newSupervisor->canAcceptMoreStudents()) {
            throw new \Exception('The selected supervisor has reached their maximum student limit.');
        }

        // Calculate new match score
        $newScore = $this->calculateMatchScore($allocation->project, $newSupervisor);

        // Update the allocation
        $allocation->update([
            'supervisor_id' => $newSupervisor->id,
            'status' => 'reassigned',
            'match_score' => $newScore,
        ]);

        return $allocation;
    }

    /**
     * Get allocation statistics.
     */
    public function getStatistics(): array
    {
        $totalProjects = Project::where('status', 'submitted')->count();
        $allocatedProjects = Allocation::where('status', 'approved')->count();
        $pendingAllocations = Allocation::where('status', 'pending')->count();
        $averageMatchScore = Allocation::whereNotNull('match_score')->avg('match_score');

        return [
            'total_projects' => $totalProjects,
            'allocated_projects' => $allocatedProjects,
            'pending_allocations' => $pendingAllocations,
            'allocation_rate' => $totalProjects > 0 ? ($allocatedProjects / $totalProjects) * 100 : 0,
            'average_match_score' => round($averageMatchScore, 2),
        ];
    }
} 