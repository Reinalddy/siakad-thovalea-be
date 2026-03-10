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
        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->string('tahun_akademik'); // cth: "2025/2026"
            $table->enum('semester', ['Ganjil', 'Genap', 'Pendek']);
            $table->enum('status', ['Draft', 'Aktif', 'Selesai'])->default('Draft');
            $table->date('krs_start')->nullable();
            $table->date('krs_end')->nullable();
            $table->date('nilai_start')->nullable();
            $table->date('nilai_end')->nullable();
            $table->date('ukt_start')->nullable();
            $table->date('ukt_end')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
    }
};
