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
        Schema::table('kpi_periods', function (Blueprint $table) {
            // Pengaturan 360 dinamis per periode (JSON):
            // min_raters_per_group, max_peers_per_evaluatee, allow_not_observed
            $table->json('settings')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_periods', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
