<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use Illuminate\Support\Collection;

final class QuestionnaireAssignmentService
{
    /**
     * Dapatkan daftar penugasan kuesioner untuk seorang pegawai pada periode aktif:
     * - P3: menilai atasan langsung + manajer (orang yang sama cukup 1x).
     * - P2: menilai rekan yang atasan langsungnya sama.
     * - P1: menilai bawahan langsung.
     *
     * Catatan Penting:
     * Tidak menyimpan record ke database (status pending) saat pegawai baru login/akses form.
     * Record penugasan hanya akan disimpan ke DB saat penilaian selesai disubmit.
     *
     * @return Collection<int, KpiAssignment>
     */
    public function getAssignmentsFor(Employee $me, int $periodId): Collection
    {
        $existingAssignments = KpiAssignment::query()
            ->where('period_id', $periodId)
            ->where('evaluator_id', $me->id)
            ->with(['evaluatee.position', 'evaluatee.office', 'scores.subcriteria'])
            ->get()
            ->keyBy('evaluatee_id');

        $result = collect();

        // 1. Atasan (P3): atasan langsung + manajer, dedup per pegawai.
        $superiorIds = collect([$me->direct_supervisor_id, $me->manager_id])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $me->id)
            ->unique()
            ->values();

        foreach ($superiorIds as $superiorId) {
            $assignment = $this->resolveAssignment($me, $periodId, $superiorId, 'P3', $existingAssignments);
            if ($assignment) {
                $result->push($assignment);
            }
        }

        // 2. Rekan satu atasan (P2), kecuali yang sudah dinilai sebagai atasan.
        if ($me->direct_supervisor_id) {
            $peerIds = Employee::query()
                ->where('direct_supervisor_id', $me->direct_supervisor_id)
                ->whereKeyNot($me->id)
                ->when($superiorIds->isNotEmpty(), fn ($q) => $q->whereKeyNot($superiorIds->all()))
                ->pluck('id');

            foreach ($peerIds as $peerId) {
                $assignment = $this->resolveAssignment($me, $periodId, (int) $peerId, 'P2', $existingAssignments);
                if ($assignment) {
                    $result->push($assignment);
                }
            }
        }

        // 3. Bawahan langsung (P1).
        $subordinateIds = Employee::query()
            ->where('direct_supervisor_id', $me->id)
            ->pluck('id');

        foreach ($subordinateIds as $subordinateId) {
            $assignment = $this->resolveAssignment($me, $periodId, (int) $subordinateId, 'P1', $existingAssignments);
            if ($assignment) {
                $result->push($assignment);
            }
        }

        return $result;
    }

    private function resolveAssignment(
        Employee $me,
        int $periodId,
        int $evaluateeId,
        string $type,
        Collection $existingAssignments
    ): ?KpiAssignment {
        if ($existingAssignments->has($evaluateeId)) {
            return $existingAssignments->get($evaluateeId);
        }

        $evaluatee = Employee::query()->with(['position', 'office'])->find($evaluateeId);
        if (! $evaluatee) {
            return null;
        }

        $assignment = new KpiAssignment;
        $assignment->period_id = $periodId;
        $assignment->evaluator_id = $me->id;
        $assignment->evaluatee_id = $evaluateeId;
        $assignment->evaluator_type = $type;
        $assignment->status = 'pending';
        $assignment->setRelation('evaluatee', $evaluatee);
        $assignment->setRelation('scores', collect());

        return $assignment;
    }
}
