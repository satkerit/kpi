<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rating_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->nullable()->constrained('kpi_periods')->cascadeOnDelete();
            $table->string('label');
            $table->decimal('min_value', 5, 2);
            $table->decimal('max_value', 5, 2);
            $table->string('color', 20)->nullable();
            $table->string('predicate');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rating_scales');
    }
};
