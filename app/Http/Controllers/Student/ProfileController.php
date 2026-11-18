<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __invoke(Request $request): View
    {
        $student = $request->user()
            ->student()
            ->with('program')
            ->first();

        return view('student.profile', compact('student'));
    }
}
