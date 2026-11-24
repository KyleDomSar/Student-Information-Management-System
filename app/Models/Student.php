<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'student_number',
        'program_id',
        'program_major_id',
        'section_id',
        'year_level',
        'status',
    ];

    protected function casts(): array
    {
        return ['year_level' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(ProgramMajor::class, 'program_major_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function yearLevelRecord(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class, 'year_level', 'level_number');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }
}
