<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $fillable = ['college_id', 'code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function college(): BelongsTo
    {
        return $this->belongsTo(College::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
