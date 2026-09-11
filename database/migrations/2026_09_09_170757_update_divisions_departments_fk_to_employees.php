<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite (testing :memory:): lewati — skema awal tetap pakai FK users,
        // kolom HR di users dibiarkan nullable agar UserFactory tetap jalan.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // Null-kan head_id yang masih merujuk users (belum ada di employees)
        DB::statement('UPDATE divisions SET head_id = NULL');
        DB::statement('UPDATE departments SET head_id = NULL');

        // divisions: drop FK lama ke users, tambah FK baru ke employees
        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
            $table->foreign('head_id')->references('id')->on('employees')->nullOnDelete();
        });

        // departments: drop FK lama ke users, tambah FK baru ke employees
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
            $table->foreign('head_id')->references('id')->on('employees')->nullOnDelete();
        });

        // Hapus kolom HR dari users (sudah pindah ke employees)
        Schema::table('users', function (Blueprint $table) {
            // SQLite tidak support dropForeign — skip pada driver sqlite
            if (DB::getDriverName() !== 'sqlite') {
                $table->dropForeign(['office_id']);
                $table->dropForeign(['division_id']);
                $table->dropForeign(['position_id']);
                $table->dropForeign(['direct_supervisor_id']);
                $table->dropForeign(['department_id']);
            }

            // Hanya drop kolom yang benar-benar ada
            $cols = ['office_id', 'division_id', 'position_id', 'nik', 'phone', 'direct_supervisor_id', 'department_id'];
            $existing = array_filter($cols, fn ($c) => Schema::hasColumn('users', $c));

            if ($existing) {
                $table->dropColumn(array_values($existing));
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('nik')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->foreignId('direct_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
            $table->foreign('head_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::table('divisions', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
            $table->foreign('head_id')->references('id')->on('users')->nullOnDelete();
        });
    }
};
