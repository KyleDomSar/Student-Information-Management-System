<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'program_id',
        'code',
        'title',
        'units',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'units' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function enrollmentSubjects(): HasMany
    {
        return $this->hasMany(EnrollmentSubject::class);
    }
}
