<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\EvaluationRule;
use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use Illuminate\Support\Collection;

final class RuleBasedAssignmentService
{
    /**
     * Generate KpiAssignment dari seluruh rule aktif untuk satu periode.
     *
     * @return array{created: int, skipped: int}
     */
    public function generateForPeriod(int $periodId): array
    {
        $rules = EvaluationRule::query()
            ->where('is_active', true)
            ->with(['evaluatorPosition', 'evaluateePosition', 'scopeOffice', 'scopeDivision', 'scopeDepartment'])
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($rules as $rule) {
            [$c, $s] = $this->expandRule($rule, $periodId);
            $created += $c;
            $skipped += $s;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }

    /**
     * Expand satu rule menjadi assignment-assignment konkret.
     *
     * @return array{int, int}
     */
    private function expandRule(EvaluationRule $rule, int $periodId): array
    {
        // Ambil kandidat evaluator: jabatan sesuai rule + filter scope kantor/divisi/bagian
        $evaluators = $this->getCandidates(
            positionId: $rule->evaluator_position_id,
            officeId: $rule->scope_office_id,
            divisionId: $rule->scope_division_id,
            departmentId: $rule->scope_department_id,
        );

        if ($evaluators->isEmpty()) {
            return [0, 0];
        }

        $created = 0;
        $skipped = 0;

        foreach ($evaluators as $evaluator) {
            $evaluatees = $this->resolveEvaluatees($rule, $evaluator);

            foreach ($evaluatees as $evaluatee) {
                if ($evaluator->id === $evaluatee->id) {
                    continue;
                }

                $result = $this->createAssignment($periodId, $evaluator->id, $evaluatee->id, $rule->evaluator_type);
                $result === 1 ? $created++ : $skipped++;
            }
        }

        return [$created, $skipped];
    }

    /**
     * Tentukan daftar pegawai yang akan dinilai oleh evaluator tertentu berdasarkan rule.
     *
     * @return Collection<int, Employee>
     */
    private function resolveEvaluatees(EvaluationRule $rule, Employee $evaluator): Collection
    {
        // Jika evaluatee_position_id diisi, pakai jabatan itu; jika tidak, pakai constraint level
        if ($rule->evaluatee_position_id !== null) {
            return $this->getCandidates(
                positionId: $rule->evaluatee_position_id,
                officeId: $rule->scope_office_id,
                divisionId: $rule->scope_division_id,
                departmentId: $rule->scope_department_id,
            );
        }

        // Fallback: filter berdasarkan constraint level tipe P1/P2/P3
        $evaluatorLevel = (int) ($evaluator->position?->level ?? 0);

        return Employee::query()
            ->with('position')
            ->whereHas('position', function ($q) use ($rule, $evaluatorLevel) {
                match ($rule->evaluator_type) {
                    'P1' => $q->where('level', '>', $evaluatorLevel),
                    'P3' => $q->where('level', '<', $evaluatorLevel),
                    'P2' => $q->where('level', $evaluatorLevel),
                    'SELF' => $q->where('level', $evaluatorLevel), // SELF: evaluator menilai dirinya sendiri
                };
            })
            ->when($rule->scope_office_id, fn ($q) => $q->where('office_id', $rule->scope_office_id))
            ->when($rule->scope_division_id, fn ($q) => $q->where('division_id', $rule->scope_division_id))
            ->when($rule->scope_department_id, fn ($q) => $q->where('department_id', $rule->scope_department_id))
            ->get();
    }

    /**
     * Ambil pegawai berdasarkan jabatan + scope opsional.
     *
     * @return Collection<int, Employee>
     */
    private function getCandidates(
        int $positionId,
        ?int $officeId,
        ?int $divisionId,
        ?int $departmentId,
    ): Collection {
        return Employee::query()
            ->with('position')
            ->where('position_id', $positionId)
            ->when($officeId, fn ($q) => $q->where('office_id', $officeId))
            ->when($divisionId, fn ($q) => $q->where('division_id', $divisionId))
            ->when($departmentId, fn ($q) => $q->where('department_id', $departmentId))
            ->get();
    }

    private function createAssignment(int $periodId, int $evaluatorId, int $evaluateeId, string $type): int
    {
        $exists = KpiAssignment::query()
            ->where('period_id', $periodId)
            ->where('evaluator_id', $evaluatorId)
            ->where('evaluatee_id', $evaluateeId)
            ->where('evaluator_type', $type)
            ->exists();

        if ($exists) {
            return 0;
        }

        KpiAssignment::query()->create([
            'period_id' => $periodId,
            'evaluator_id' => $evaluatorId,
            'evaluatee_id' => $evaluateeId,
            'evaluator_type' => $type,
            'status' => 'pending',
        ]);

        return 1;
    }
}
