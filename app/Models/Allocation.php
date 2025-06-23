<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Allocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'supervisor_id',
        'student_id',
        'status',
        'admin_notes',
        'rejection_reason',
        'match_score',
        'allocated_at',
    ];

    protected $casts = [
        'match_score' => 'decimal:2',
        'allocated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the allocation.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the supervisor that owns the allocation.
     */
    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(Supervisor::class);
    }

    /**
     * Get the student that owns the allocation.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
} 