<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use App\Models\Project;
use App\Models\Student;
use App\Models\Supervisor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AllocationController extends Controller
{
    /**
     * Display a listing of the allocations.
     */
    public function index(): Response
    {
        $allocations = Allocation::with([
            'project.student.user',
            'supervisor.user',
            'student.user'
        ])->get();

        return Inertia::render('Admin/Allocations/Index', [
            'allocations' => $allocations,
        ]);
    }

    /**
     * Show the form for creating a new allocation.
     */
    public function create(): Response
    {
        $students = Student::with(['user', 'department'])
            ->whereDoesntHave('allocation', function ($query) {
                $query->where('status', 'approved');
            })
            ->get();

        $supervisors = Supervisor::with(['user', 'department', 'specializations'])
            ->where('is_active', true)
            ->get();

        return Inertia::render('Admin/Allocations/Create', [
            'students' => $students,
            'supervisors' => $supervisors,
        ]);
    }

    /**
     * Store a newly created allocation in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'supervisor_id' => 'required|exists:supervisors,id',
            'project_id' => 'required|exists:projects,id',
            'admin_notes' => 'nullable|string',
        ]);

        // Check if student already has an approved allocation
        $existingAllocation = Allocation::where('student_id', $validated['student_id'])
            ->where('status', 'approved')
            ->first();

        if ($existingAllocation) {
            return back()->withErrors(['student_id' => 'This student already has an approved allocation.']);
        }

        // Check if supervisor can accept more students
        $supervisor = Supervisor::find($validated['supervisor_id']);
        if (!$supervisor->canAcceptMoreStudents()) {
            return back()->withErrors(['supervisor_id' => 'This supervisor has reached their maximum student limit.']);
        }

        Allocation::create([
            'student_id' => $validated['student_id'],
            'supervisor_id' => $validated['supervisor_id'],
            'project_id' => $validated['project_id'],
            'status' => 'pending',
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('admin.allocations.index')
            ->with('success', 'Allocation created successfully.');
    }

    /**
     * Display the specified allocation.
     */
    public function show(Allocation $allocation): Response
    {
        $allocation->load([
            'project.student.user',
            'supervisor.user',
            'student.user'
        ]);

        return Inertia::render('Admin/Allocations/Show', [
            'allocation' => $allocation,
        ]);
    }

    /**
     * Show the form for editing the specified allocation.
     */
    public function edit(Allocation $allocation): Response
    {
        $allocation->load([
            'project.student.user',
            'supervisor.user',
            'student.user'
        ]);

        $supervisors = Supervisor::with(['user', 'department'])
            ->where('is_active', true)
            ->get();

        return Inertia::render('Admin/Allocations/Edit', [
            'allocation' => $allocation,
            'supervisors' => $supervisors,
        ]);
    }

    /**
     * Update the specified allocation in storage.
     */
    public function update(Request $request, Allocation $allocation)
    {
        $validated = $request->validate([
            'supervisor_id' => 'required|exists:supervisors,id',
            'status' => 'required|in:pending,approved,rejected,reassigned',
            'admin_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string|required_if:status,rejected',
        ]);

        // If changing supervisor, check if new supervisor can accept more students
        if ($validated['supervisor_id'] !== $allocation->supervisor_id) {
            $supervisor = Supervisor::find($validated['supervisor_id']);
            if (!$supervisor->canAcceptMoreStudents()) {
                return back()->withErrors(['supervisor_id' => 'This supervisor has reached their maximum student limit.']);
            }
        }

        $allocation->update([
            'supervisor_id' => $validated['supervisor_id'],
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'],
            'rejection_reason' => $validated['rejection_reason'],
            'allocated_at' => $validated['status'] === 'approved' ? now() : null,
        ]);

        return redirect()->route('admin.allocations.index')
            ->with('success', 'Allocation updated successfully.');
    }

    /**
     * Remove the specified allocation from storage.
     */
    public function destroy(Allocation $allocation)
    {
        $allocation->delete();

        return redirect()->route('admin.allocations.index')
            ->with('success', 'Allocation deleted successfully.');
    }

    /**
     * Approve an allocation.
     */
    public function approve(Allocation $allocation)
    {
        $allocation->update([
            'status' => 'approved',
            'allocated_at' => now(),
        ]);

        return redirect()->route('admin.allocations.index')
            ->with('success', 'Allocation approved successfully.');
    }

    /**
     * Reject an allocation.
     */
    public function reject(Request $request, Allocation $allocation)
    {
        $validated = $request->validate([
            'rejection_reason' => 'required|string',
        ]);

        $allocation->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.allocations.index')
            ->with('success', 'Allocation rejected successfully.');
    }
} 