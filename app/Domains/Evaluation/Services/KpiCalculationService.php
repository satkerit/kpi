<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Models\KpiResult;
use App\Domains\MasterKpi\Models\KpiEvaluatorWeight;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Domains\MasterKpi\Models\RatingScale;
use Illuminate\Support\Facades\DB;

final class KpiCalculationService
{
    /**
     * Hitung nilai akhir KPI untuk satu pegawai pada satu periode.
     *
     * Formula: Final = Σ(rata-rata tipe × bobot tipe) / Σ(bobot tipe yang ada datanya)
     * Bobot diambil dari kpi_evaluator_weights per periode (fallback = 1.0 semua sama).
     */
    public function processFinalScore(int $evaluateeId, int $periodId): KpiResult
    {
        return DB::transaction(function () use ($evaluateeId, $periodId) {
            $weights = $this->loadWeights($periodId);
            $period = KpiPeriod::query()->find($periodId);
            $minRaters = (int) ($period?->setting('min_raters_per_group') ?? KpiPeriod::DEFAULT_SETTINGS['min_raters_per_group']);
            $types = ['P1', 'P2', 'P3', 'SELF'];

            $typeScores = [];
            foreach ($types as $type) {
                // SELF dikecualikan dari ambang anonim (selalu 1 penilai = diri sendiri).
                $threshold = $type === 'SELF' ? 1 : $minRaters;
                $avg = $this->calculateAverageForType($evaluateeId, $periodId, $type, $threshold);
                if ($avg > 0) {
                    $typeScores[$type] = $avg;
                }
            }

            $finalScore = $this->computeWeightedFinal($typeScores, $weights);
            $predicate = $this->determinePredicate($finalScore, $periodId);

            return KpiResult::query()->updateOrCreate(
                ['period_id' => $periodId, 'evaluatee_id' => $evaluateeId],
                [
                    'score_p1' => $typeScores['P1'] ?? 0,
                    'score_p2' => $typeScores['P2'] ?? 0,
                    'score_p3' => $typeScores['P3'] ?? 0,
                    'score_self' => $typeScores['SELF'] ?? 0,
                    'final_score' => $finalScore,
                    'predicate' => $predicate,
                ]
            );
        });
    }

    /**
     * Hitung rata-rata skor untuk satu tipe evaluator.
     */
    /**
     * Hitung rata-rata skor untuk satu tipe evaluator.
     * Grup dengan jumlah penilai < ambang anonymity tidak dilaporkan (kembali 0).
     * Item "Tidak Diamati" (raw_score null) dikeluarkan dari pembobotan lalu
     * bobot dinormalisasi ulang agar total tetap pada skala yang sama.
     */
    private function calculateAverageForType(int $evaluateeId, int $periodId, string $evaluatorType, int $minRaters = 1): float
    {
        $assignments = KpiAssignment::query()
            ->with(['scores.subcriteria'])
            ->where('period_id', $periodId)
            ->where('evaluatee_id', $evaluateeId)
            ->where('evaluator_type', $evaluatorType)
            ->where('status', 'submitted')
            ->whereHas('scores')
            ->get();

        if ($assignments->count() < $minRaters) {
            return 0.0;
        }

        $total = 0.0;
        foreach ($assignments as $assignment) {
            $scored = $assignment->scores->filter(fn($s) => $s->raw_score !== null);
            $weightSum = $scored->sum(fn($s) => (float) ($s->subcriteria->weight ?? 0));

            if ($weightSum <= 0) {
                continue;
            }

            // bobot ternormalisasi: Σ(raw × weight) / Σ(weight)
            $total += $scored->sum(fn($s) => (float) $s->raw_score * (float) ($s->subcriteria->weight ?? 0)) / $weightSum;
        }

        return round($total / $assignments->count(), 2);
    }

    /**
     * Hitung nilai akhir berbobot dari semua tipe yang memiliki data.
     *
     * @param  array<string, float>  $typeScores
     * @param  array<string, float>  $weights
     */
    private function computeWeightedFinal(array $typeScores, array $weights): float
    {
        if (empty($typeScores)) {
            return 0.0;
        }

        $weightedSum = 0.0;
        $totalWeight = 0.0;

        foreach ($typeScores as $type => $score) {
            $w = $weights[$type] ?? 1.0;
            $weightedSum += $score * $w;
            $totalWeight += $w;
        }

        if ($totalWeight === 0.0) {
            return 0.0;
        }

        return round($weightedSum / $totalWeight, 2);
    }

    /**
     * Muat bobot evaluator dari DB per periode.
     * Fallback ke 1.0 jika belum dikonfigurasi.
     *
     * @return array<string, float>
     */
    private function loadWeights(int $periodId): array
    {
        $rows = KpiEvaluatorWeight::query()
            ->where('period_id', $periodId)
            ->pluck('weight', 'evaluator_type');

        return $rows->mapWithKeys(fn($w, $type) => [$type => (float) $w])->toArray();
    }

    /**
     * Tentukan predikat dari rating_scales periode (dinamis).
     * Fallback ke rentang bawaan jika belum ada konfigurasi.
     */
    public function determinePredicate(float $score, ?int $periodId = null): string
    {
        if ($periodId !== null) {
            $scale = RatingScale::query()
                ->where('period_id', $periodId)
                ->where('min_value', '<=', $score)
                ->where('max_value', '>=', $score)
                ->orderByDesc('min_value')
                ->first();

            if ($scale !== null) {
                return $scale->predicate;
            }
        }

        // Fallback default — sinkron dengan DefaultPeriodConfigSeeder.
        return match (true) {
            $score >= 8.5 => 'Sangat Baik',
            $score >= 7.0 => 'Baik',
            $score >= 5.5 => 'Cukup Baik',
            $score >= 3.5 => 'Buruk',
            default => 'Sangat Buruk',
        };
    }
}
