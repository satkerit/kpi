<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_evaluator_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('kpi_periods')->cascadeOnDelete();
            $table->enum('evaluator_type', ['P1', 'P2', 'P3', 'SELF']);
            $table->decimal('weight', 5, 2)->default(1.00)->comment('Bobot relatif, misal 1.0 = sama rata, 1.5 = lebih tinggi');
            $table->timestamps();

            $table->unique(['period_id', 'evaluator_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluator_weights');
    }
};
