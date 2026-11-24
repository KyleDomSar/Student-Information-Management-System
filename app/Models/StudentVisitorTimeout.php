<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentVisitorTimeout extends Model
{
    protected $fillable = [
        'time_out_at',
    ];

    protected function casts(): array
    {
        return [
            'time_out_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
