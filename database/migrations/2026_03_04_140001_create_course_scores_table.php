<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_scores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('schedule_id')->constrained('schedules')->cascadeOnDelete();
            $table->foreignUuid('krs_period_id')->constrained('krs_periods')->cascadeOnDelete();

            // Raw Inputs
            $table->float('score_attendance')->default(0);
            $table->float('score_assignment')->default(0);
            $table->float('score_uts')->default(0);
            $table->float('score_uas')->default(0);

            // Computed
            $table->float('final_score_numeric')->nullable();
            $table->string('final_score_letter', 5)->nullable();
            $table->float('final_weight')->nullable(); // Grade point equivalent (4.0, 3.5, etc)

            $table->timestamps();

            // Prevent multiple score entries for same student/schedule combo
            $table->unique(['student_id', 'schedule_id'], 'student_schedule_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_scores');
    }
};
