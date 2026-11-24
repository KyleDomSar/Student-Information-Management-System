<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YearLevel extends Model
{
    protected $fillable = [
        'level_number',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level_number' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'year_level', 'level_number');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class, 'year_level', 'level_number');
    }

    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(
            Program::class,
            'program_year_levels',
            'year_level',
            'program_id',
            'level_number',
            'id',
        )->withTimestamps();
    }
}
