<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;

final class AssignmentGeneratorService
{
    /**
     * Generate penugasan untuk seluruh pegawai pada periode tertentu:
     * - P1: atasan langsung menilai bawahannya.
     * - P3: pegawai menilai atasan langsung + manajer (orang yang sama cukup 1x).
     *
     * @return array{p1: int, p3: int}
     */
    public function generateForPeriod(int $periodId): array
    {
        $employees = Employee::query()
            ->where(fn ($q) => $q->whereNotNull('direct_supervisor_id')->orWhereNotNull('manager_id'))
            ->get(['id', 'direct_supervisor_id', 'manager_id']);

        $createdP1 = 0;
        $createdP3 = 0;

        foreach ($employees as $employee) {
            // Atasan langsung menilai pegawai (P1)
            if ($employee->direct_supervisor_id) {
                $createdP1 += $this->createAssignment($periodId, $employee->direct_supervisor_id, $employee->id, 'P1');
            }

            // Pegawai menilai atasan langsung dan manajer (P3) — dedup: orang sama cukup 1x.
            $superiorIds = collect([$employee->direct_supervisor_id, $employee->manager_id])
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0 && $id !== $employee->id)
                ->unique();

            foreach ($superiorIds as $superiorId) {
                $createdP3 += $this->createAssignment($periodId, $employee->id, $superiorId, 'P3');
            }
        }

        return ['p1' => $createdP1, 'p3' => $createdP3];
    }

    /**
     * Generate penugasan P2: pegawai saling menilai dengan rekan yang
     * atasan langsungnya sama (kecuali atasan/manajer — sudah dinilai via P3).
     * Jumlah penilai per pegawai dibatasi `max_peers_per_evaluatee`.
     *
     * @return int Jumlah penugasan P2 yang baru dibuat.
     */
    public function generatePeersForPeriod(int $periodId): int
    {
        $period = KpiPeriod::query()->find($periodId);
        $maxPeers = (int) ($period?->setting('max_peers_per_evaluatee') ?? KpiPeriod::DEFAULT_SETTINGS['max_peers_per_evaluatee']);

        $groups = Employee::query()
            ->whereNotNull('direct_supervisor_id')
            ->get(['id', 'direct_supervisor_id', 'manager_id'])
            ->groupBy('direct_supervisor_id');

        $created = 0;

        foreach ($groups as $supervisorId => $group) {
            $superiorIds = collect([$supervisorId])
                ->merge($group->pluck('manager_id'))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique();

            foreach ($group as $evaluatee) {
                $peers = $group
                    ->reject(fn (Employee $p) => $p->id === $evaluatee->id || $superiorIds->contains((int) $p->id))
                    ->pluck('id');

                $selected = $maxPeers > 0 ? $peers->random(min($maxPeers, $peers->count())) : $peers;

                foreach ($selected as $peerId) {
                    $created += $this->createAssignment($periodId, (int) $peerId, $evaluatee->id, 'P2');
                }
            }
        }

        return $created;
    }

    private function createAssignment(int $periodId, int $evaluatorId, int $evaluateeId, string $type): int
    {
        // Anti-double global: satu evaluator menilai satu orang cukup sekali
        // per periode, apa pun tipenya.
        $exists = KpiAssignment::query()
            ->where('period_id', $periodId)
            ->where('evaluator_id', $evaluatorId)
            ->where('evaluatee_id', $evaluateeId)
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
