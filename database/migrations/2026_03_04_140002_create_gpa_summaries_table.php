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
        Schema::create('gpa_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('krs_period_id')->constrained('krs_periods')->cascadeOnDelete();

            // Semester Stats
            $table->float('ips')->default(0);
            $table->integer('total_sks_semester')->default(0);

            // Cumulative Stats up to this period
            $table->float('ipk')->default(0);
            $table->integer('total_sks_cumulative')->default(0);

            $table->timestamps();

            // One summary per student per semester
            $table->unique(['student_id', 'krs_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gpa_summaries');
    }
};
