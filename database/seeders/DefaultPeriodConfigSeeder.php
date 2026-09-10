<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domains\MasterKpi\Models\KpiEvaluatorWeight;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Domains\MasterKpi\Models\RatingScale;
use Illuminate\Database\Seeder;

/**
 * Seed bobot evaluator dan skala penilaian default untuk setiap periode yang belum dikonfigurasi.
 * Aman dijalankan berulang (upsert/firstOrCreate).
 */
final class DefaultPeriodConfigSeeder extends Seeder
{
    /** @var array<string, float> Bobot default per tipe evaluator */
    private array $defaultWeights = [
        'P1' => 1.50, // Atasan langsung — bobot lebih tinggi sesuai teori 360
        'P2' => 1.00, // Rekan sejawat
        'P3' => 0.75, // Bawahan — bobot lebih rendah
        'SELF' => 0.75, // Penilaian diri — bobot lebih rendah
    ];

    /**
     * Skala predikat default (0–10).
     *
     * @var array<int, array{label: string, min: float, max: float, predicate: string, color: string, sort_order: int}>
     */
    private array $defaultScales = [
        ['label' => 'Sangat Baik',  'min' => 8.50, 'max' => 10.00, 'predicate' => 'Sangat Baik',  'color' => '#16a34a', 'sort_order' => 1],
        ['label' => 'Baik',         'min' => 7.00, 'max' => 8.49,  'predicate' => 'Baik',         'color' => '#2563eb', 'sort_order' => 2],
        ['label' => 'Cukup Baik',   'min' => 5.50, 'max' => 6.99,  'predicate' => 'Cukup Baik',   'color' => '#d97706', 'sort_order' => 3],
        ['label' => 'Buruk',        'min' => 3.50, 'max' => 5.49,  'predicate' => 'Buruk',         'color' => '#dc2626', 'sort_order' => 4],
        ['label' => 'Sangat Buruk', 'min' => 0.00, 'max' => 3.49,  'predicate' => 'Sangat Buruk',  'color' => '#7f1d1d', 'sort_order' => 5],
    ];

    public function run(): void
    {
        $periods = KpiPeriod::all();

        if ($periods->isEmpty()) {
            $this->command->warn('Belum ada periode KPI. Buat periode terlebih dahulu lalu jalankan seeder ini kembali.');

            return;
        }

        foreach ($periods as $period) {
            $this->seedWeights($period);
            $this->seedScales($period);
            $this->command->info("Periode [{$period->name}]: bobot + skala default diterapkan.");
        }
    }

    private function seedWeights(KpiPeriod $period): void
    {
        foreach ($this->defaultWeights as $type => $weight) {
            KpiEvaluatorWeight::firstOrCreate(
                ['period_id' => $period->id, 'evaluator_type' => $type],
                ['weight' => $weight],
            );
        }
    }

    private function seedScales(KpiPeriod $period): void
    {
        foreach ($this->defaultScales as $scale) {
            RatingScale::firstOrCreate(
                ['period_id' => $period->id, 'predicate' => $scale['predicate']],
                [
                    'label' => $scale['label'],
                    'min_value' => $scale['min'],
                    'max_value' => $scale['max'],
                    'color' => $scale['color'],
                    'sort_order' => $scale['sort_order'],
                ],
            );
        }
    }
}
