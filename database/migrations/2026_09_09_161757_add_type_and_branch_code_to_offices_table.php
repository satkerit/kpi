<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            // Jenis kantor: branch = cabang, kas = kantor kas, head_office = pusat, kpo = KPO
            $table->enum('type', ['head_office', 'branch', 'kpo', 'kas'])->default('branch')->after('code');
            // Kode cabang induk — wajib diisi jika type = 'kas'
            $table->string('branch_code', 20)->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['type', 'branch_code']);
        });
    }
};
