<?php

declare(strict_types=1);

namespace App\Domains\Evaluation\Services;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class QuestionnaireAssignmentService
{
    /**
     * Batas level jabatan yang dianggap grade pimpinan
     * (Pimpinan Cabang / Kepala Kas / Kepala Divisi dst).
     */
    private const LEADER_LEVEL_MAX = 4;

    /**
     * Pastikan pegawai punya penugasan kuesioner pada periode aktif:
     * - P3: menilai 1 atasan langsung
     * - P2: menilai 1 rekan selevel (masih 1 kantor atau 1 bagian)
     * - P1: menilai bawahan; grade pimpinan menilai SELURUH staff di bawahnya
     *       (pegawai biasa hanya bawahan langsung).
     *
     * @return int Jumlah penugasan baru yang dibuat.
     */
    public function ensureFor(Employee $me, int $periodId): int
    {
        $created = 0;

        // 1. Atasan langsung (P3)
        if ($me->direct_supervisor_id) {
            $created += $this->create($periodId, $me->id, $me->direct_supervisor_id, 'P3');
        }

        // 2. Satu rekan selevel, masih 1 kantor / 1 bagian (P2)
        $peer = $this->sameScope($me)
            ->whereKeyNot($me->id)
            ->whereHas('position', fn ($q) => $q->where('level', $me->position?->level))
            ->orderBy('name')
            ->first();

        if ($peer) {
            $created += $this->create($periodId, $me->id, $peer->id, 'P2');
        }

        // 3. Bawahan (P1)
        foreach ($this->subordinateTargets($me) as $subordinate) {
            $created += $this->create($periodId, $me->id, $subordinate->id, 'P1');
        }

        // 4. Penilaian diri sendiri (SELF)
        $created += $this->create($periodId, $me->id, $me->id, 'SELF');

        return $created;
    }

    /**
     * Target bawahan: bawahan langsung untuk pegawai biasa,
     * seluruh staff di bawahnya untuk grade pimpinan.
     *
     * @return Collection<int, Employee>
     */
    private function subordinateTargets(Employee $me): Collection
    {
        $targets = $me->subordinates()->get();

        if (! $this->isLeaderGrade($me)) {
            return $targets;
        }

        // Telusuri seluruh hierarki di bawahnya (BFS), tetap 1 kantor / 1 bagian.
        $all = $targets->keyBy('id');
        $frontier = $targets;

        while ($frontier->isNotEmpty()) {
            $ids = $frontier->pluck('id')->all();

            $next = Employee::query()
                ->whereIn('direct_supervisor_id', $ids)
                ->whereKeyNot($all->keys()->all())
                ->get()
                ->filter(fn (Employee $e) => $this->inSameScope($me, $e));

            $next->each(fn (Employee $e) => $all->put($e->id, $e));
            $frontier = $next;
        }

        return $all->values();
    }

    private function isLeaderGrade(Employee $me): bool
    {
        $level = (int) ($me->position?->level ?? 0);

        return $level > 0 && $level <= self::LEADER_LEVEL_MAX;
    }

    private function inSameScope(Employee $me, Employee $other): bool
    {
        return ($me->office_id && $me->office_id === $other->office_id)
            || ($me->department_id && $me->department_id === $other->department_id);
    }

    /**
     * Query pegawai yang masih 1 kantor atau 1 bagian dengan pegawai pembanding.
     */
    private function sameScope(Employee $me): Builder
    {
        return Employee::query()->where(function ($w) use ($me) {
            $w->where(function ($q) use ($me) {
                $me->office_id
                    ? $q->where('office_id', $me->office_id)
                    : $q->whereRaw('1 = 0');
            })->orWhere(function ($q) use ($me) {
                $me->department_id
                    ? $q->where('department_id', $me->department_id)
                    : $q->whereRaw('1 = 0');
            });
        });
    }

    private function create(int $periodId, int $evaluatorId, int $evaluateeId, string $type): int
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
