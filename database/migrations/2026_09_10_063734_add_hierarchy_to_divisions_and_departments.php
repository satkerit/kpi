<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('divisions', function (Blueprint $table): void {
            // Sub-divisi: parent_id merujuk ke divisi induk (nullable = divisi utama)
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('divisions')
                ->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table): void {
            // Sub-bagian: parent_id merujuk ke bagian induk (nullable = bagian utama)
            $table->foreignId('parent_id')
                ->nullable()
                ->after('id')
                ->constrained('departments')
                ->nullOnDelete();

            // Kategori: operasional atau bisnis
            $table->enum('category', ['operasional', 'bisnis'])
                ->nullable()
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'category']);
        });

        Schema::table('divisions', function (Blueprint $table): void {
            $table->dropForeign(['parent_id']);
            $table->dropColumn('parent_id');
        });
    }
};
