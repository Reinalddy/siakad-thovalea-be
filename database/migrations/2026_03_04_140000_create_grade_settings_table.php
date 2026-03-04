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
        Schema::create('grade_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('grade_letter', 5)->unique(); // A, B, C, D, E, B+
            $table->float('weight'); // 4.0, 3.5
            $table->float('min_score'); // 85.0
            $table->float('max_score'); // 100.0
            $table->boolean('is_pass')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_settings');
    }
};
