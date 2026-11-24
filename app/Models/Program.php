<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    protected $fillable = [
        'college_id',
        'department_id',
        'code',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function majors(): HasMany
    {
        return $this->hasMany(ProgramMajor::class);
    }

    public function yearLevels(): BelongsToMany
    {
        return $this->belongsToMany(
            YearLevel::class,
            'program_year_levels',
            'program_id',
            'year_level',
            'id',
            'level_number',
        )->withTimestamps();
    }
}
