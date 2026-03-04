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
        Schema::create('student_bills', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignUuid('payment_category_id')->constrained('payment_categories')->cascadeOnDelete();

            // Map bill to a specific academic period if applicable (like UKT for Ganjil 2025/2026)
            $table->foreignUuid('krs_period_id')->nullable()->constrained('krs_periods')->nullOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('due_date');

            // Expected string values: Unpaid, Partial, Paid, Overdue
            $table->string('status')->default('Unpaid');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_bills');
    }
};
