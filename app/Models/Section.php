<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    protected $fillable = [
        'program_id',
        'year_level',
        'code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'year_level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function yearLevel(): BelongsTo
    {
        return $this->belongsTo(YearLevel::class, 'year_level', 'level_number');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
