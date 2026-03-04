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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('student_bill_id')->constrained('student_bills')->cascadeOnDelete();

            $table->string('transaction_id')->unique()->nullable(); // ID returned from Payment Gateway (Midtrans/Xendit)
            $table->string('payment_method')->nullable(); // e.g., VA_BCA, EWALLET_GOPAY

            $table->decimal('amount_paid', 15, 2);
            $table->timestamp('paid_at')->nullable();

            // Pending, Success, Failed, Expired
            $table->string('status')->default('Pending');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
