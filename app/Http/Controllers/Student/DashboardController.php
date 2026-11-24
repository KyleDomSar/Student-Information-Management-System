<?php

namespace App\\Http\\Controllers\\Student;

use App\\Http\\Controllers\\Controller;
use Illuminate\\Http\\Request;
use Illuminate\\View\\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()
            ->student()
            ->with([
                'program',
                'section',
                'yearLevelRecord',
                'visitorTimeouts' => fn ($query) => $query->latest('time_out_at')->limit(5),
                'enrollments' => fn ($query) => $query->latest()->limit(5),
            ])
            ->first();

        return view('student.dashboard', compact('student'));
    }
}
