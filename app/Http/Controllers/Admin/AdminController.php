<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Notifications\AdminLoginDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     */
    public function index(): Response
    {
        $admins = User::where('role', RoleEnum::ADMIN->value)
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('admin/admins/Index', [
            'admins' => $admins,
        ]);
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create(): Response
    {
        return Inertia::render('admin/admins/Create');
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
        ]);

        $generatedPassword = str()->random(16);
        $loginUrl = route('login');
        
        DB::transaction(function () use ($validated, $generatedPassword, $loginUrl) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($generatedPassword),
                'role' => RoleEnum::ADMIN->value,
            ]);

            // Send login details notification
            $user->notify(new AdminLoginDetails($generatedPassword, $loginUrl));
        });

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin created successfully.');
    }

    /**
     * Display the specified admin.
     */
    public function show(User $admin): Response
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        return Inertia::render('admin/admins/Show', [
            'admin' => $admin,
        ]);
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(User $admin): Response
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        return Inertia::render('admin/admins/Edit', [
            'admin' => $admin,
        ]);
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, User $admin)
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
        ]);

        $admin->update($validated);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin updated successfully.');
    }

    /**
     * Ban the specified admin.
     */
    public function ban(User $admin)
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        // Prevent self-ban
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot ban yourself.');
        }

        $admin->update(['banned_at' => now()]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin has been banned.');
    }

    /**
     * Unban the specified admin.
     */
    public function unban(User $admin)
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        // Prevent self-unban (though this is less critical)
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot unban yourself.');
        }

        $admin->update(['banned_at' => null]);

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin has been unbanned.');
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(User $admin)
    {
        // Ensure it's an admin
        if ($admin->role !== RoleEnum::ADMIN->value) {
            abort(404);
        }

        // Prevent self-deletion
        if ($admin->id === Auth::id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'You cannot delete yourself.');
        }

        $admin->delete();

        return redirect()->route('admin.admins.index')
            ->with('success', 'Admin deleted successfully.');
    }
}
