<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;

final class AssignmentGeneratorService
{
    /**
     * Generate penugasan P1 (atasan langsung) & P3 (bawahan langsung)
     * berdasarkan kolom direct_supervisor_id untuk seluruh pegawai pada periode tertentu.
     *
     * @return array{p1: int, p3: int}
     */
    public function generateForPeriod(int $periodId): array
    {
        $employees = Employee::query()
            ->whereNotNull('direct_supervisor_id')
            ->get();

        $createdP1 = 0;
        $createdP3 = 0;

        foreach ($employees as $employee) {
            // Atasan langsung menilai pegawai (P1)
            $createdP1 += $this->createAssignment($periodId, $employee->direct_supervisor_id, $employee->id, 'P1');

            // Pegawai menilai atasan langsungnya (P3)
            $createdP3 += $this->createAssignment($periodId, $employee->id, $employee->direct_supervisor_id, 'P3');
        }

        return ['p1' => $createdP1, 'p3' => $createdP3];
    }

    /**
     * Generate penugasan P2 untuk pegawai selevel (bisa lintas kantor/divisi).
     * Jumlah penilai per pegawai dibatasi pengaturan periode `max_peers_per_evaluatee`
     * (standar 360: ~6 penilai; mencegah ledakan kombinasi O(n^2)).
     *
     * @return int Jumlah penugasan P2 yang baru dibuat.
     */
    public function generatePeersForPeriod(int $periodId): int
    {
        $period = KpiPeriod::query()->find($periodId);
        $maxPeers = (int) ($period?->setting('max_peers_per_evaluatee') ?? KpiPeriod::DEFAULT_SETTINGS['max_peers_per_evaluatee']);

        $employees = Employee::query()
            ->with('position')
            ->whereHas('position')
            ->get()
            ->groupBy(fn(Employee $e) => (int) $e->position->level);

        $created = 0;

        foreach ($employees as $levelGroup) {
            foreach ($levelGroup as $evaluatee) {
                $peers = $levelGroup->reject(fn(Employee $p) => $p->id === $evaluatee->id);

                if ($maxPeers > 0 && $peers->count() > $maxPeers) {
                    $peers = $peers->random($maxPeers);
                }

                foreach ($peers as $peer) {
                    $created += $this->createAssignment($periodId, $peer->id, $evaluatee->id, 'P2');
                }
            }
        }

        return $created;
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
