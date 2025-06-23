<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the student's projects.
     */
    public function index(): Response
    {
        $student = Auth::user()->student;
        $projects = $student->projects()->with('specializations')->get();

        return Inertia::render('Student/Projects/Index', [
            'projects' => $projects,
        ]);
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): Response
    {
        $specializations = Specialization::all();

        return Inertia::render('Student/Projects/Create', [
            'specializations' => $specializations,
        ]);
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'methodology' => 'nullable|string',
            'specialization_ids' => 'array',
            'specialization_ids.*' => 'exists:specializations,id',
        ]);

        $student = Auth::user()->student;

        $project = Project::create([
            'student_id' => $student->id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'objectives' => $validated['objectives'],
            'methodology' => $validated['methodology'],
            'status' => 'draft',
        ]);

        if (!empty($validated['specialization_ids'])) {
            $project->specializations()->attach($validated['specialization_ids']);
        }

        return redirect()->route('student.projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): Response
    {
        // Ensure the project belongs to the authenticated student
        if ($project->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $project->load(['specializations', 'allocation.supervisor.user']);

        return Inertia::render('Student/Projects/Show', [
            'project' => $project,
        ]);
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project): Response
    {
        // Ensure the project belongs to the authenticated student
        if ($project->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $specializations = Specialization::all();
        $project->load('specializations');

        return Inertia::render('Student/Projects/Edit', [
            'project' => $project,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Update the specified project in storage.
     */
    public function update(Request $request, Project $project)
    {
        // Ensure the project belongs to the authenticated student
        if ($project->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'methodology' => 'nullable|string',
            'specialization_ids' => 'array',
            'specialization_ids.*' => 'exists:specializations,id',
        ]);

        $project->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'objectives' => $validated['objectives'],
            'methodology' => $validated['methodology'],
        ]);

        $project->specializations()->sync($validated['specialization_ids'] ?? []);

        return redirect()->route('student.projects.index')
            ->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project)
    {
        // Ensure the project belongs to the authenticated student
        if ($project->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('student.projects.index')
            ->with('success', 'Project deleted successfully.');
    }

    /**
     * Submit the project for allocation.
     */
    public function submit(Project $project)
    {
        // Ensure the project belongs to the authenticated student
        if ($project->student_id !== Auth::user()->student->id) {
            abort(403);
        }

        $project->update(['status' => 'submitted']);

        return redirect()->route('student.projects.index')
            ->with('success', 'Project submitted successfully for allocation.');
    }
} 