<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Specialization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get the supervisors for the specialization.
     */
    public function supervisors(): BelongsToMany
    {
        return $this->belongsToMany(Supervisor::class, 'supervisor_specialization');
    }

    /**
     * Get the projects for the specialization.
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_specialization');
    }
} 