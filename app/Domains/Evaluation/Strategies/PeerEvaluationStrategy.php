<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Strategies;

use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;
use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Support\Collection;

final class PeerEvaluationStrategy implements EvaluatorStrategyInterface
{
    public function evaluatorType(): string
    {
        return 'P2';
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
            ->where('evaluator_type', 'P2')
            ->with('criteria')
            ->get();
    }

    /**
     * P2: Rekan Sejawat — memiliki atasan langsung yang sama, bukan orang yang sama.
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool
    {
        if ($evaluatorId === $evaluateeId) {
            return false;
        }

        $evaluator = Employee::query()->find($evaluatorId);

        return $evaluator !== null
            && $evaluator->direct_supervisor_id !== null
            && (int) $evaluator->direct_supervisor_id === (int) Employee::query()->find($evaluateeId)?->direct_supervisor_id;
    }
}
