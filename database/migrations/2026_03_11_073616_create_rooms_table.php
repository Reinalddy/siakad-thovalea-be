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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruang')->unique(); // cth: A.1.1, LAB-01
            $table->string('nama_ruang'); // cth: Ruang Teori A, Laboratorium Komputer
            $table->integer('kapasitas'); // cth: 40
            $table->enum('jenis_ruang', ['Teori', 'Laboratorium']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
