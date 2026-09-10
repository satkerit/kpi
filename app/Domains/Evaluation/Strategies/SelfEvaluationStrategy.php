<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Strategies;

use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;
use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Support\Collection;

final class SelfEvaluationStrategy implements EvaluatorStrategyInterface
{
    public function evaluatorType(): string
    {
        return 'SELF';
    }

    public function calculate(KpiAssignment $assignment): float
    {
        $scores = $assignment->relationLoaded('scores') ? $assignment->scores : $assignment->scores()->get();

        $total = 0.0;
        foreach ($scores as $score) {
            $total += (float) $score->weighted_score;
        }

        return round($total, 2);
    }

    public function validSubcriteria(): Collection
    {
        return KpiSubcriteria::query()
            ->where('evaluator_type', 'SELF')
            ->with('criteria')
            ->get();
    }

    /**
     * SELF: evaluator dan evaluatee harus orang yang sama.
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool
    {
        return $evaluatorId === $evaluateeId;
    }
}
