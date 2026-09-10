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
        Schema::create('kpi_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->foreignId('evaluatee_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('score_p1', 8, 2)->default(0);
            $table->decimal('score_p2', 8, 2)->default(0);
            $table->decimal('score_p3', 8, 2)->default(0);
            $table->decimal('final_score', 8, 2)->default(0);
            $table->string('predicate');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_results');
    }
};
