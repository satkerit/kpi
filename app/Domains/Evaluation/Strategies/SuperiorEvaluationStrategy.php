<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Strategies;

use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;
use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Support\Collection;

final class SuperiorEvaluationStrategy implements EvaluatorStrategyInterface
{
    public function evaluatorType(): string
    {
        return 'P1';
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
            ->where('evaluator_type', 'P1')
            ->with('criteria')
            ->get();
    }

    /**
     * P1: Atasan menilai bawahan — evaluatee harus ber-atasan langsung ke evaluator.
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool
    {
        $evaluatee = Employee::query()->find($evaluateeId);

        return $evaluatee !== null
            && (int) $evaluatee->direct_supervisor_id === $evaluatorId;
    }
}
