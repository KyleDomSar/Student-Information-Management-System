<?php

namespace App\\Models;

use Illuminate\\Database\\Eloquent\\Model;
use Illuminate\\Database\\Eloquent\\Relations\\HasMany;

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
}
