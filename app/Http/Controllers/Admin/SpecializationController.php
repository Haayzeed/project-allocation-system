<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the specializations.
     */
    public function index(): Response
    {
        $specializations = Specialization::withCount(['supervisors', 'projects'])->get();

        return Inertia::render('Admin/Specializations/Index', [
            'specializations' => $specializations,
        ]);
    }

    /**
     * Show the form for creating a new specialization.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Specializations/Create');
    }

    /**
     * Store a newly created specialization in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Specialization::create($validated);

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization created successfully.');
    }

    /**
     * Display the specified specialization.
     */
    public function show(Specialization $specialization): Response
    {
        $specialization->load(['supervisors.user', 'projects.student.user']);

        return Inertia::render('Admin/Specializations/Show', [
            'specialization' => $specialization,
        ]);
    }

    /**
     * Show the form for editing the specified specialization.
     */
    public function edit(Specialization $specialization): Response
    {
        return Inertia::render('Admin/Specializations/Edit', [
            'specialization' => $specialization,
        ]);
    }

    /**
     * Update the specified specialization in storage.
     */
    public function update(Request $request, Specialization $specialization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $specialization->update($validated);

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization updated successfully.');
    }

    /**
     * Remove the specified specialization from storage.
     */
    public function destroy(Specialization $specialization)
    {
        $specialization->delete();

        return redirect()->route('admin.specializations.index')
            ->with('success', 'Specialization deleted successfully.');
    }
} 