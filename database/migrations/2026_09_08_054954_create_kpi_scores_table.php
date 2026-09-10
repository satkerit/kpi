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
        Schema::create('kpi_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('kpi_assignments')->cascadeOnDelete();
            $table->foreignId('subcriteria_id')->constrained('kpi_subcriteria')->cascadeOnDelete();
            $table->decimal('raw_score', 8, 2);
            $table->decimal('weighted_score', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_scores');
    }
};
