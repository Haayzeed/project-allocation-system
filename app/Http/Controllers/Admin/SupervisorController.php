<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Specialization;
use App\Models\Supervisor;
use App\Models\User;
use App\Enums\RoleEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SupervisorController extends Controller
{
    public function index(): Response
    {
        $supervisors = User::where('role', 'supervisor')
            ->with(['supervisor', 'supervisor.department', 'supervisor.specializations'])
            ->get();
        
        $departments = Department::all();
        $specializations = Specialization::all();
        
        return Inertia::render('admin/supervisors/Index', [
            'supervisors' => $supervisors,
            'departments' => $departments,
            'specializations' => $specializations,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'title' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'specialization_ids' => 'required|array|min:1',
            'specialization_ids.*' => 'required|exists:specializations,id',
            'staff_id' => 'required|string|max:255|unique:supervisors',
            'bio' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(str()->random(16)),
                'role' => RoleEnum::SUPERVISOR->value,
            ]);

            $supervisor = Supervisor::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
                'title' => $validated['title'],
                'staff_id' => $validated['staff_id'],
                'bio' => $validated['bio'],
            ]);

            $supervisor->specializations()->attach($validated['specialization_ids']);
        });

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor created successfully.');
    }

    public function edit(User $supervisor): Response
    {
        // Ensure it's a supervisor
        if ($supervisor->role !== RoleEnum::SUPERVISOR->value) {
            abort(404);
        }
        
        $supervisor->load(['supervisor', 'supervisor.department', 'supervisor.specializations']);
        $departments = Department::all();
        $specializations = Specialization::all();
        
        return Inertia::render('admin/supervisors/Edit', [
            'supervisor' => $supervisor,
            'departments' => $departments,
            'specializations' => $specializations,
        ]);
    }

    public function update(Request $request, User $supervisor)
    {
        // Ensure it's a supervisor
        if ($supervisor->role !== RoleEnum::SUPERVISOR->value) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $supervisor->id,
            'title' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'specialization_ids' => 'required|array|min:1',
            'specialization_ids.*' => 'required|exists:specializations,id',
            'staff_id' => 'required|string|max:255|unique:supervisors,staff_id,' . $supervisor->supervisor->id,
            'bio' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($supervisor, $validated) {
            $supervisor->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            $supervisor->supervisor->update([
                'department_id' => $validated['department_id'],
                'title' => $validated['title'],
                'staff_id' => $validated['staff_id'],
                'bio' => $validated['bio'],
            ]);

            $supervisor->supervisor->specializations()->sync($validated['specialization_ids']);
        });

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor updated successfully.');
    }

    public function destroy(User $supervisor)
    {
        // Ensure it's a supervisor
        if ($supervisor->role !== RoleEnum::SUPERVISOR->value) {
            abort(404);
        }
        
        // Check if supervisor has any allocations
        if ($supervisor->supervisor && $supervisor->supervisor->allocations()->count() > 0) {
            return redirect()->route('admin.supervisors.index')
                ->with('error', 'Cannot delete supervisor that has student allocations.');
        }

        $supervisor->delete();

        return redirect()->route('admin.supervisors.index')
            ->with('success', 'Supervisor deleted successfully.');
    }
} 