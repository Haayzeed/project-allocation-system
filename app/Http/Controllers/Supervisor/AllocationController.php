<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Allocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AllocationController extends Controller
{
    /**
     * Display a listing of the supervisor's allocations.
     */
    public function index(Request $request): Response
    {
        $supervisor = Auth::user()->supervisor;
        
        $query = $supervisor->allocations()
            ->with(['student.user', 'project.specializations'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by project area/specialization
        if ($request->has('specialization_id') && $request->specialization_id !== '') {
            $query->whereHas('project.specializations', function ($q) use ($request) {
                $q->where('specializations.id', $request->specialization_id);
            });
        }

        $allocations = $query->get();

        return Inertia::render('Supervisor/Allocations/Index', [
            'allocations' => $allocations,
            'filters' => $request->only(['status', 'specialization_id']),
        ]);
    }

    /**
     * Display the specified allocation.
     */
    public function show(Allocation $allocation): Response
    {
        // Ensure the allocation belongs to the authenticated supervisor
        if ($allocation->supervisor_id !== Auth::user()->supervisor->id) {
            abort(403);
        }

        $allocation->load([
            'student.user',
            'project.specializations',
            'project.student.department'
        ]);

        return Inertia::render('Supervisor/Allocations/Show', [
            'allocation' => $allocation,
        ]);
    }

    /**
     * Show the form for editing the specified allocation.
     */
    public function edit(Allocation $allocation): Response
    {
        // Ensure the allocation belongs to the authenticated supervisor
        if ($allocation->supervisor_id !== Auth::user()->supervisor->id) {
            abort(403);
        }

        $allocation->load([
            'student.user',
            'project.specializations',
            'project.student.department'
        ]);

        return Inertia::render('Supervisor/Allocations/Edit', [
            'allocation' => $allocation,
        ]);
    }

    /**
     * Update the specified allocation in storage.
     */
    public function update(Request $request, Allocation $allocation)
    {
        // Ensure the allocation belongs to the authenticated supervisor
        if ($allocation->supervisor_id !== Auth::user()->supervisor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string',
        ]);

        $allocation->update([
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('supervisor.allocations.index')
            ->with('success', 'Allocation updated successfully.');
    }
} 