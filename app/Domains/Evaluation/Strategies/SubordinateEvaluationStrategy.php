<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Strategies;

use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;
use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Support\Collection;

final class SubordinateEvaluationStrategy implements EvaluatorStrategyInterface
{
    public function evaluatorType(): string
    {
        return 'P3';
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
            ->where('evaluator_type', 'P3')
            ->with('criteria')
            ->get();
    }

    /**
     * P3: Bawahan menilai atasan — evaluator harus menunjuk evaluatee
     * sebagai atasan langsung ATAU manajer (cukup salah satu, 1x penilaian).
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool
    {
        $evaluator = Employee::query()->find($evaluatorId);

        if ($evaluator === null) {
            return false;
        }

        return (int) $evaluator->direct_supervisor_id === $evaluateeId
            || (int) $evaluator->manager_id === $evaluateeId;
    }
}
