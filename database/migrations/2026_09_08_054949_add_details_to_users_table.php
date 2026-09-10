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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->string('nik')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->foreignId('direct_supervisor_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['office_id']);
            $table->dropForeign(['division_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['direct_supervisor_id']);
            $table->dropColumn([
                'office_id',
                'division_id',
                'position_id',
                'nik',
                'phone',
                'direct_supervisor_id',
            ]);
        });
    }
};
