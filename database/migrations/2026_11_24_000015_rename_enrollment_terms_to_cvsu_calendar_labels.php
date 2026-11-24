<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('enrollments')->where('semester', 'First')->update(['semester' => 'First Semester']);
        DB::table('enrollments')->where('semester', 'Second')->update(['semester' => 'Second Semester']);
        DB::table('enrollments')->where('semester', 'Summer')->update(['semester' => 'Midyear']);
    }

    public function down(): void
    {
        DB::table('enrollments')->where('semester', 'First Semester')->update(['semester' => 'First']);
        DB::table('enrollments')->where('semester', 'Second Semester')->update(['semester' => 'Second']);
        DB::table('enrollments')->where('semester', 'Midyear')->update(['semester' => 'Summer']);
    }
};
