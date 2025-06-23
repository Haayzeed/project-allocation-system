<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Specialization;
use App\Models\Supervisor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class SupervisorController extends Controller
{
    /**
     * Display a listing of the supervisors.
     */
    public function index(): Response
    {
        $supervisors = Supervisor::with(['user', 'department', 'specializations'])
            ->withCount(['allocations' => function ($query) {
                $query->where('status', 'approved');
            }])
            ->get();

        return Inertia::render('Admin/Supervisors/Index', [
            'supervisors' => $supervisors,
        ]);
    }

    /**
     * Show the form for creating a new supervisor.
     */
    public function create(): Response
    {
        $departments = Department::all();
        $specializations = Specialization::all();

        return Inertia::render('Admin/Supervisors/Create', [
            'departments' => $departments,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Store a newly created supervisor in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'required|exists:departments,id',
            'staff_id' => 'required|string|max:50|unique:supervisors',
            'title' => 'required|string|max:50',
            'bio' => 'nullable|string',
            'max_students' => 'required|integer|min:1|max:20',
            'specialization_ids' => 'array',
            'specialization_ids.*' => 'exists:specializations,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'supervisor',
        ]);

        $supervisor = Supervisor::create([
            'user_id' => $user->id,
            'department_id' => $validated['department_id'],
            'staff_id' => $validated['staff_id'],
            'title' => $validated['title'],
            'bio' => $validated['bio'],
            'max_students' => $validated['max_students'],
        ]);

        if (!empty($validated['specialization_ids'])) {
            $supervisor->specializations()->attach($validated['specialization_ids']);
        }

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor created successfully.');
    }

    /**
     * Display the specified supervisor.
     */
    public function show(Supervisor $supervisor): Response
    {
        $supervisor->load(['user', 'department', 'specializations', 'allocations.student.user', 'allocations.project']);

        return Inertia::render('Admin/Supervisors/Show', [
            'supervisor' => $supervisor,
        ]);
    }

    /**
     * Show the form for editing the specified supervisor.
     */
    public function edit(Supervisor $supervisor): Response
    {
        $departments = Department::all();
        $specializations = Specialization::all();
        $supervisor->load(['user', 'specializations']);

        return Inertia::render('Admin/Supervisors/Edit', [
            'supervisor' => $supervisor,
            'departments' => $departments,
            'specializations' => $specializations,
        ]);
    }

    /**
     * Update the specified supervisor in storage.
     */
    public function update(Request $request, Supervisor $supervisor)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $supervisor->user_id,
            'department_id' => 'required|exists:departments,id',
            'staff_id' => 'required|string|max:50|unique:supervisors,staff_id,' . $supervisor->id,
            'title' => 'required|string|max:50',
            'bio' => 'nullable|string',
            'max_students' => 'required|integer|min:1|max:20',
            'is_active' => 'boolean',
            'specialization_ids' => 'array',
            'specialization_ids.*' => 'exists:specializations,id',
        ]);

        $supervisor->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $supervisor->update([
            'department_id' => $validated['department_id'],
            'staff_id' => $validated['staff_id'],
            'title' => $validated['title'],
            'bio' => $validated['bio'],
            'max_students' => $validated['max_students'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        $supervisor->specializations()->sync($validated['specialization_ids'] ?? []);

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor updated successfully.');
    }

    /**
     * Remove the specified supervisor from storage.
     */
    public function destroy(Supervisor $supervisor)
    {
        $supervisor->user->delete(); // This will cascade delete the supervisor

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor deleted successfully.');
    }
} 