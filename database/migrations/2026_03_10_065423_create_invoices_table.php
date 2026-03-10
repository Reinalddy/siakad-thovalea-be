<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_invoice')->unique();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_period_id')->constrained('academic_periods')->cascadeOnDelete();
            $table->decimal('jumlah_tagihan', 12, 2);
            $table->enum('status', ['Belum Bayar', 'Lunas', 'Menunggak'])->default('Belum Bayar');
            $table->timestamp('tanggal_lunas')->nullable();
            $table->string('metode_pembayaran')->nullable(); // cth: "VA Mandiri", "QRIS"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
