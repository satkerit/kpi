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
        Schema::table('kpi_scores', function (Blueprint $table) {
            // Komentar kualitatif per subkriteria (umpan balik 360).
            $table->text('comment')->nullable()->after('weighted_score');
            // raw_score menjadi nullable untuk opsi "Tidak Diamati" (not observed).
            $table->decimal('raw_score', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_scores', function (Blueprint $table) {
            $table->dropColumn('comment');
            $table->decimal('raw_score', 8, 2)->change();
        });
    }
};
