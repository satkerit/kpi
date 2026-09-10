<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Interfaces;

use App\Domains\Evaluation\Models\KpiAssignment;
use Illuminate\Support\Collection;

interface EvaluatorStrategyInterface
{
    /**
     * Get the evaluator type string: P1, P2, or P3.
     */
    public function evaluatorType(): string;

    /**
     * Calculate total weighted score for an assignment.
     */
    public function calculate(KpiAssignment $assignment): float;

    /**
     * Get valid subcriteria collection for this evaluator type.
     */
    public function validSubcriteria(): Collection;

    /**
     * Validate organizational relationship between evaluator and evaluatee.
     * Enforces strictly direct (max 1-level) relationship.
     */
    public function validateRelationship(int $evaluatorId, int $evaluateeId): bool;
}
