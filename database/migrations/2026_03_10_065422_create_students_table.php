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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dosen_pa_id')->nullable()->constrained('lecturers')->nullOnDelete(); // Dosen Pembimbing Akademik
            $table->string('nim')->unique();
            $table->string('prodi');
            $table->year('angkatan');
            $table->enum('status_mahasiswa', ['Aktif', 'Cuti', 'Lulus', 'DO']);
            $table->decimal('ipk', 3, 2)->default(0.00); // Untuk batas maksimal SKS
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
