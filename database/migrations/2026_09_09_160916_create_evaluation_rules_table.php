<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evaluation_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->enum('evaluator_type', ['P1', 'P2', 'P3', 'SELF']);

            // Jabatan penilai (wajib)
            $table->foreignId('evaluator_position_id')
                ->constrained('positions')
                ->cascadeOnDelete();

            // Jabatan yang dinilai (null = semua jabatan yang sesuai constraint level)
            $table->foreignId('evaluatee_position_id')
                ->nullable()
                ->constrained('positions')
                ->nullOnDelete();

            // Scope lingkup penilaian — nullable berarti berlaku semua
            $table->foreignId('scope_office_id')
                ->nullable()
                ->constrained('offices')
                ->nullOnDelete();

            $table->foreignId('scope_division_id')
                ->nullable()
                ->constrained('divisions')
                ->nullOnDelete();

            $table->foreignId('scope_department_id')
                ->nullable()
                ->constrained('departments')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluation_rules');
    }
};
