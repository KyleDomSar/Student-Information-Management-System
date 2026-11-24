<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * The unique student/academic-term index is already created by
     * 2026_11_18_000006_create_enrollments_table.php.
     *
     * Keep this migration as a no-op so databases that received this
     * migration file can complete their migration history without
     * attempting to create the same index twice.
     */
    public function up(): void
    {
        //
    }

    public function down(): void
    {
        //
    }
};
