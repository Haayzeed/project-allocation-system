<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Student;
use App\Models\User;
use App\Notifications\StudentLoginDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $students = User::where('role', 'student')
            ->with(['student', 'student.department', 'student.allocation.supervisor.user'])
            ->paginate(10);
        $departments = Department::all();
        return Inertia::render('admin/students/Index', [
            'students' => $students,
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
            'department_id' => 'required|exists:departments,id',
            'student_id' => 'required|string|max:50|unique:students',
            'level' => 'required|string|max:10',
            'session' => 'required|string|max:20',
        ]);

        $generatedPassword = str()->random(16);
        $loginUrl = route('login');
        
        DB::transaction(function () use ($validated, $generatedPassword, $loginUrl) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($generatedPassword),
                'role' => RoleEnum::STUDENT->value,
            ]);

            Student::create([
                'user_id' => $user->id,
                'department_id' => $validated['department_id'],
                'student_id' => $validated['student_id'],
                'level' => $validated['level'],
                'session' => $validated['session'],
            ]);

            // Send login details notification
            $user->notify(new StudentLoginDetails($generatedPassword, $loginUrl));
        });

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
        if ($student->allocation) {
            return redirect()->route('admin.students.index')
                ->with('error', 'Student has an allocation and cannot be deleted.');
        }

        $student->user->delete();

        return redirect()->route('admin.students.index')
            ->with('success', 'Student deleted successfully.');
    }
} 