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
     * P2: Rekan Sejawat (level jabatan setara, lintas/sama divisi & kantor, bukan orang yang sama).
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool
    {
        if ($evaluatorId === $evaluateeId) {
            return false;
        }

        $evaluator = Employee::query()->with('position')->find($evaluatorId);
        $evaluatee = Employee::query()->with('position')->find($evaluateeId);

        if (! $evaluator || ! $evaluatee || ! $evaluator->position || ! $evaluatee->position) {
            return false;
        }

        // Posisi tingkat setara
        return (int) $evaluator->position->level === (int) $evaluatee->position->level;
    }
}
