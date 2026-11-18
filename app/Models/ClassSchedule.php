<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSchedule extends Model
{
    protected $fillable = [
        'enrollment_subject_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
    ];

    public function enrollmentSubject(): BelongsTo
    {
        return $this->belongsTo(EnrollmentSubject::class);
    }
}
