<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('class_schedules');
        Schema::dropIfExists('enrollment_subjects');
        Schema::dropIfExists('subjects');

        Schema::dropIfExists('program_year_levels');

        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_major_id');
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('college_id');
        });

        Schema::dropIfExists('program_majors');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('colleges');

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        // Keep existing student and section references intact. Remove unused seeded
        // programs when no student or section points to them, and archive the rest.
        $ceitProgramCodes = ['BSABE', 'BSArch', 'BSCE', 'BSCpE', 'BSCS', 'BSEE', 'BSECE', 'BSIE', 'BSIndTech', 'BSIT'];

        DB::table('programs')->whereNotIn('code', $ceitProgramCodes)->update(['is_active' => false]);

        $unusedProgramIds = DB::table('programs')
            ->whereNotIn('code', $ceitProgramCodes)
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('students')
                    ->whereColumn('students.program_id', 'programs.id');
            })
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')->from('sections')
                    ->whereColumn('sections.program_id', 'programs.id');
            })
            ->pluck('id');

        if ($unusedProgramIds->isNotEmpty()) {
            DB::table('programs')->whereIn('id', $unusedProgramIds)->delete();
        }
    }

    public function down(): void
    {
        Schema::create('colleges', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('campus', 100)->default('Main Campus');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['campus', 'name']);
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('college_id')->constrained()->restrictOnDelete();
            $table->string('code', 30)->unique();
            $table->string('name', 255);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['college_id', 'name']);
        });

        Schema::table('programs', function (Blueprint $table) {
            $table->foreignId('college_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
        });

        Schema::create('program_majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['program_id', 'name']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('program_major_id')->nullable()->constrained('program_majors')->nullOnDelete();
        });

        Schema::create('program_year_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('year_level');
            $table->timestamps();
            $table->unique(['program_id', 'year_level']);
            $table->foreign('year_level')->references('level_number')->on('year_levels')->cascadeOnDelete();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->string('status', 30)->default('enrolled')->index();
        });

        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code', 40)->unique();
            $table->string('title');
            $table->unsignedTinyInteger('units');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('enrollment_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('instructor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('final_grade', 5, 2)->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
            $table->unique(['enrollment_id', 'subject_id']);
        });

        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_subject_id')->constrained()->cascadeOnDelete();
            $table->string('day_of_week', 15);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room', 100)->nullable();
            $table->timestamps();
            $table->index(['day_of_week', 'start_time', 'end_time']);
        });
    }
};
