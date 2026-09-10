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
        // SQLite (testing :memory:): lewati — FK tetap ke users, enum jadi TEXT tanpa constraint.
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // kpi_assignments: pindah FK evaluatee_id & evaluator_id ke employees, tambah SELF type
        Schema::table('kpi_assignments', function (Blueprint $table) {
            $table->dropForeign(['evaluatee_id']);
            $table->dropForeign(['evaluator_id']);
            $table->foreign('evaluatee_id')->references('id')->on('employees')->cascadeOnDelete();
            $table->foreign('evaluator_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // Ubah enum evaluator_type di kpi_assignments tambah SELF
        DB::statement("ALTER TABLE kpi_assignments MODIFY evaluator_type ENUM('P1','P2','P3','SELF') NOT NULL");

        // kpi_results: pindah FK evaluatee_id ke employees
        Schema::table('kpi_results', function (Blueprint $table) {
            $table->dropForeign(['evaluatee_id']);
            $table->foreign('evaluatee_id')->references('id')->on('employees')->cascadeOnDelete();
        });

        // Ubah enum evaluator_type di kpi_subcriteria tambah SELF
        DB::statement("ALTER TABLE kpi_subcriteria MODIFY evaluator_type ENUM('P1','P2','P3','SELF') NOT NULL");

        // Ubah enum evaluator_type di evaluation_rules tambah SELF
        DB::statement("ALTER TABLE evaluation_rules MODIFY evaluator_type ENUM('P1','P2','P3','SELF') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE evaluation_rules MODIFY evaluator_type ENUM('P1','P2','P3') NOT NULL");
        DB::statement("ALTER TABLE kpi_subcriteria MODIFY evaluator_type ENUM('P1','P2','P3') NOT NULL");
        DB::statement("ALTER TABLE kpi_assignments MODIFY evaluator_type ENUM('P1','P2','P3') NOT NULL");

        Schema::table('kpi_results', function (Blueprint $table) {
            $table->dropForeign(['evaluatee_id']);
            $table->foreign('evaluatee_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('kpi_assignments', function (Blueprint $table) {
            $table->dropForeign(['evaluatee_id']);
            $table->dropForeign(['evaluator_id']);
            $table->foreign('evaluatee_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('evaluator_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
