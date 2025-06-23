<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supervisor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'staff_id',
        'title',
        'bio',
        'max_students',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the supervisor.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department that owns the supervisor.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the specializations for the supervisor.
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'supervisor_specialization');
    }

    /**
     * Get the allocations for the supervisor.
     */
    public function allocations(): HasMany
    {
        return $this->hasMany(Allocation::class);
    }

    /**
     * Get the current number of students assigned to this supervisor.
     */
    public function getCurrentStudentCountAttribute(): int
    {
        return $this->allocations()
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Check if the supervisor can accept more students.
     */
    public function canAcceptMoreStudents(): bool
    {
        return $this->current_student_count < $this->max_students;
    }
} 