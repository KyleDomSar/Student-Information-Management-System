<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
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
            $table->foreignId('college_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->after('college_id')->constrained()->nullOnDelete();
        });

        Schema::create('program_majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['program_id', 'name']);
        });

        Schema::create('program_year_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('year_level');
            $table->timestamps();
            $table->unique(['program_id', 'year_level']);
            $table->foreign('year_level')->references('level_number')->on('year_levels')->cascadeOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('program_major_id')->nullable()->after('program_id')->constrained('program_majors')->nullOnDelete();
        });

        // Preserve combinations already represented by student and section records.
        $existingCombinations = DB::table('sections')
            ->select('program_id', 'year_level')
            ->union(
                DB::table('students')
                    ->select('program_id', 'year_level')
                    ->whereNotNull('program_id')
                    ->whereNotNull('year_level')
            )
            ->get();

        foreach ($existingCombinations as $combination) {
            DB::table('program_year_levels')->insertOrIgnore([
                'program_id' => $combination->program_id,
                'year_level' => $combination->year_level,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('program_major_id');
        });

        Schema::dropIfExists('program_year_levels');
        Schema::dropIfExists('program_majors');

        Schema::table('programs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('college_id');
        });

        Schema::dropIfExists('departments');
        Schema::dropIfExists('colleges');
    }
};
