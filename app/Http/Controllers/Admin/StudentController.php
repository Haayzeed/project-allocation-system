<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class StudentController extends Controller
{
    /**
     * Display a listing of the students.
     */
    public function index(): Response
    {
        $students = Student::with(['user', 'department', 'allocation.supervisor.user'])
            ->withCount('projects')
            ->get();

        return Inertia::render('Admin/Students/Index', [
            'students' => $students,
        ]);
    }

    /**
     * Show the form for creating a new student.
     */
    public function create(): Response
    {
        $departments = Department::all();

        return Inertia::render('Admin/Students/Create', [
            'departments' => $departments,
        ]);
    }

    /**
     * Store a newly created student in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'department_id' => 'required|exists:departments,id',
            'student_id' => 'required|string|max:50|unique:students',
            'level' => 'required|string|max:10',
            'session' => 'required|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
        ]);

        Student::create([
            'user_id' => $user->id,
            'department_id' => $validated['department_id'],
            'student_id' => $validated['student_id'],
            'level' => $validated['level'],
            'session' => $validated['session'],
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified student.
     */
    public function show(Student $student): Response
    {
        $student->load(['user', 'department', 'projects', 'allocation.supervisor.user']);

        return Inertia::render('Admin/Students/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified student.
     */
    public function edit(Student $student): Response
    {
        $departments = Department::all();
        $student->load('user');

        return Inertia::render('Admin/Students/Edit', [
            'student' => $student,
            'departments' => $departments,
        ]);
    }

    /**
     * Update the specified student in storage.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $student->user_id,
            'department_id' => 'required|exists:departments,id',
            'student_id' => 'required|string|max:50|unique:students,student_id,' . $student->id,
            'level' => 'required|string|max:10',
            'session' => 'required|string|max:20',
        ]);

        $student->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $student->update([
            'department_id' => $validated['department_id'],
            'student_id' => $validated['student_id'],
            'level' => $validated['level'],
            'session' => $validated['session'],
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified student from storage.
     */
    public function destroy(Student $student)
    {
        $student->user->delete(); // This will cascade delete the student

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
} 