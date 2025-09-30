<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'title',
        'description',
        'objectives',
        'methodology',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];
    /**
     * Get the student that owns the project.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the specializations for the project.
     */
    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'project_specialization');
    }

    /**
     * Get the allocation for the project.
     */
    public function allocation(): HasOne
    {
        return $this->hasOne(Allocation::class);
    }
} 