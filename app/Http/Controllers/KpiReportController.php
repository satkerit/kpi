<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Domains\MasterKpi\Models\KpiSubcriteria;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class KpiReportController extends Controller
{
    /**
     * Laporan KPI: daftar seluruh pegawai pada periode yang sama.
     * Pegawai tanpa hasil tetap tampil (belum dihitung).
     */
    public function index(Request $request): View
    {
        $periods = KpiPeriod::query()->orderByDesc('year')->get();
        $selectedPeriodId = (int) $request->query('period_id', $periods->first()?->id ?? 0);
        $selectedPeriod = $periods->firstWhere('id', $selectedPeriodId);

        $offices = Office::query()->where('is_active', true)->get();
        $divisions = Division::query()->where('is_active', true)->get();

        $employees = Employee::query()
            ->with([
                'office',
                'division',
                'position',
                'kpiResults' => fn ($q) => $q->where('period_id', $selectedPeriodId),
            ])
            ->withCount([
                'assignmentsAsEvaluatee as submitted_count' => fn ($q) => $q
                    ->where('period_id', $selectedPeriodId)
                    ->where('status', 'submitted'),
                'assignmentsAsEvaluatee as pending_count' => fn ($q) => $q
                    ->where('period_id', $selectedPeriodId)
                    ->where('status', 'pending'),
            ])
            ->when($request->filled('office_id'), fn ($q) => $q->where('office_id', $request->query('office_id')))
            ->when($request->filled('division_id'), fn ($q) => $q->where('division_id', $request->query('division_id')))
            ->where('is_active', true)
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $pendingCount = KpiAssignment::query()
            ->where('period_id', $selectedPeriodId)
            ->where('status', 'pending')
            ->count();

        // Ambil detail rincian per kriteria jika ada permintaan detail modal/print
        $detailEmployeeId = (int) $request->query('detail_id', 0);
        $detailData = null;
        if ($detailEmployeeId > 0 && $selectedPeriodId > 0) {
            $detailData = $this->getEmployeeDetailScore($detailEmployeeId, $selectedPeriodId);
        }

        return view('kpi.report', compact(
            'periods',
            'selectedPeriodId',
            'selectedPeriod',
            'offices',
            'divisions',
            'employees',
            'pendingCount',
            'detailData'
        ));
    }

    /**
     * Ambil rincian nilai breakdown subkriteria & penilai per role (P1, P2, P3)
     * sesuai format SK Direksi No. 016/SK-Dir/BSB.02/XII/2024.
     */
    private function getEmployeeDetailScore(int $employeeId, int $periodId): ?array
    {
        $employee = Employee::with(['office', 'division', 'position', 'directSupervisor', 'manager'])->find($employeeId);
        if (! $employee) {
            return null;
        }

        $assignments = KpiAssignment::with(['evaluator.position', 'evaluator.office', 'scores.subcriteria.criteria'])
            ->where('period_id', $periodId)
            ->where('evaluatee_id', $employeeId)
            ->where('status', 'submitted')
            ->get();

        if ($assignments->isEmpty()) {
            return [
                'employee' => $employee,
                'has_scores' => false,
            ];
        }

        // Kelompokkan penilai per role P1, P2, P3
        $raters = [
            'P1' => $assignments->where('evaluator_type', 'P1')->pluck('evaluator')->unique('id'),
            'P2' => $assignments->where('evaluator_type', 'P2')->pluck('evaluator')->unique('id'),
            'P3' => $assignments->where('evaluator_type', 'P3')->pluck('evaluator')->unique('id'),
        ];

        // Rata-rata skor per subkriteria per tipe
        $scoresByType = ['P1' => [], 'P2' => [], 'P3' => []];

        foreach (['P1', 'P2', 'P3'] as $type) {
            $typeAssignments = $assignments->where('evaluator_type', $type);
            if ($typeAssignments->isEmpty()) {
                continue;
            }

            $subcriteriaScores = [];
            foreach ($typeAssignments as $asm) {
                foreach ($asm->scores as $score) {
                    if ($score->raw_score === null || ! $score->subcriteria) {
                        continue;
                    }
                    $scId = $score->subcriteria_id;
                    $subcriteriaScores[$scId][] = (float) $score->raw_score;
                }
            }

            // Subkriteria master untuk tipe ini (termasuk yang null evaluator_type jika ada data skor terkait)
            $typeSubcriteria = KpiSubcriteria::with('criteria')
                ->where(function ($q) use ($type) {
                    $q->where('evaluator_type', $type)
                        ->orWhereNull('evaluator_type');
                })
                ->get();

            // Jika tidak ketemu dengan filter evaluator_type, ambil dari subkriteria yang benar-benar dinilai oleh assignment tipe ini
            if ($typeSubcriteria->isEmpty()) {
                $scIdsFromScores = collect($subcriteriaScores)->keys();
                $typeSubcriteria = KpiSubcriteria::with('criteria')
                    ->whereIn('id', $scIdsFromScores)
                    ->get();
            }

            $totalWeighted = 0.0;
            $items = [];
            foreach ($typeSubcriteria as $sc) {
                $rawList = $subcriteriaScores[$sc->id] ?? [];
                $avgRaw = count($rawList) > 0 ? (array_sum($rawList) / count($rawList)) : 0.0;
                $weighted = ($avgRaw * (float) $sc->weight) / 100.0;
                $totalWeighted += $weighted;

                $items[] = [
                    'criteria_name' => $sc->criteria?->name ?? '—',
                    'subcriteria_name' => $sc->name,
                    'weight' => (float) $sc->weight,
                    'avg_raw' => round($avgRaw, 1),
                    'weighted_score' => round($weighted, 1),
                ];
            }

            $scoresByType[$type] = [
                'items' => $items,
                'total_weighted' => round($totalWeighted, 1),
            ];
        }

        $p1Total = round($scoresByType['P1']['total_weighted'] ?? 0.0, 1);
        $p2Total = round($scoresByType['P2']['total_weighted'] ?? 0.0, 1);
        $p3Total = round($scoresByType['P3']['total_weighted'] ?? 0.0, 1);

        $countTypes = 0;
        if (isset($scoresByType['P1']['items']) && count($scoresByType['P1']['items']) > 0) {
            $countTypes++;
        }
        if (isset($scoresByType['P2']['items']) && count($scoresByType['P2']['items']) > 0) {
            $countTypes++;
        }
        if (isset($scoresByType['P3']['items']) && count($scoresByType['P3']['items']) > 0) {
            $countTypes++;
        }

        $finalSum = round($p1Total + $p2Total + $p3Total, 1);
        $finalScore = $countTypes > 0 ? round($finalSum / $countTypes, 1) : 0.0;

        // Tentukan predikat
        $predicate = match (true) {
            $finalScore > 9.5 => 'Sangat Baik',
            $finalScore > 7.5 => 'Baik',
            $finalScore > 5.5 => 'Cukup Baik',
            $finalScore > 3.5 => 'Buruk',
            default => 'Sangat Buruk',
        };

        return [
            'employee' => $employee,
            'has_scores' => true,
            'raters' => $raters,
            'scores_by_type' => $scoresByType,
            'p1_total' => $p1Total,
            'p2_total' => $p2Total,
            'p3_total' => $p3Total,
            'final_sum' => $finalSum,
            'final_score' => $finalScore,
            'predicate' => $predicate,
        ];
    }
}
