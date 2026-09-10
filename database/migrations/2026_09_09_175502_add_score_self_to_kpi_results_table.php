<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kpi_results', function (Blueprint $table) {
            $table->decimal('score_self', 8, 2)->default(0)->after('score_p3');
        });
    }

    public function down(): void
    {
        Schema::table('kpi_results', function (Blueprint $table) {
            if (Schema::hasColumn('kpi_results', 'score_self')) {
                $table->dropColumn('score_self');
            }
        });
    }
};
