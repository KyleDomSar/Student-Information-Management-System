<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained()->restrictOnDelete();
            $table->unsignedTinyInteger('year_level');
            $table->string('code', 30);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['program_id', 'year_level', 'code'], 'sections_program_year_code_unique');
            $table->foreign('year_level')->references('level_number')->on('year_levels')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
