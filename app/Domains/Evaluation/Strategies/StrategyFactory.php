<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Strategies;

use App\Domains\Evaluation\Interfaces\EvaluatorStrategyInterface;
use InvalidArgumentException;

final class StrategyFactory
{
    public static function make(string $evaluatorType): EvaluatorStrategyInterface
    {
        return match (strtoupper($evaluatorType)) {
            'P1' => new SuperiorEvaluationStrategy,
            'P2' => new PeerEvaluationStrategy,
            'P3' => new SubordinateEvaluationStrategy,
            'SELF' => new SelfEvaluationStrategy,
            default => throw new InvalidArgumentException("Tipe evaluator {$evaluatorType} tidak valid."),
        };
    }
}
