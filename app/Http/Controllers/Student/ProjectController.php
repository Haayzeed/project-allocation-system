<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ProjectController extends Controller
{
    /**
     * Store a newly created project.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'objectives' => 'nullable|string',
            'methodology' => 'nullable|string',
        ]);

        $student = Auth::user()->student;
        
        if (!$student) {
            throw ValidationException::withMessages([
                'error' => 'Student profile not found.'
            ]);
        }

        // Check if student already has a submitted project
        $existingProject = $student->projects()
            ->where('status', '!=', 'draft')
            ->first();

        if ($existingProject) {
            throw ValidationException::withMessages([
                'error' => 'You have already submitted a project topic.'
            ]);
        }

        $project = Project::create([
            'student_id' => $student->id,
            'title' => $request->title,
            'description' => $request->description,
            'objectives' => $request->objectives,
            'methodology' => $request->methodology,
            'status' => 'submitted',
        ]);

        return redirect()->back()->with('success', 'Project topic submitted successfully!');
    }
}