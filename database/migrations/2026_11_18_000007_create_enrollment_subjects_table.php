<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_subjects');
    }
};
