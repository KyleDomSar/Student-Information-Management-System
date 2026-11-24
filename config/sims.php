<?php

return [
    'seed_admin' => [
        'name' => env('SIMS_ADMIN_NAME', 'SIMS Administrator'),
        'email' => env('SIMS_ADMIN_EMAIL'),
        'password' => env('SIMS_ADMIN_PASSWORD'),
    ],

    'seed_student' => [
        'name' => env('SIMS_STUDENT_NAME', 'Demo Student'),
        'email' => env('SIMS_STUDENT_EMAIL'),
        'password' => env('SIMS_STUDENT_PASSWORD'),
        'student_number' => env('SIMS_STUDENT_NUMBER'),
    ],

    'grade_scale' => [
        'minimum' => (float) env('SIMS_GRADE_MIN', 0),
        'maximum' => (float) env('SIMS_GRADE_MAX', 100),
    ],
];
