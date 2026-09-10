<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Models\KpiResult;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Office;
use App\Domains\MasterKpi\Models\KpiPeriod;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class KpiDashboardController extends Controller
{
    /**
     * Dashboard rekapitulasi nilai dan status penilaian multi kantor & divisi.
     */
    public function index(Request $request): View
    {
        $periods = KpiPeriod::query()->orderByDesc('year')->get();
        $selectedPeriodId = $request->query('period_id', $periods->first()?->id);

        $offices = Office::query()->where('is_active', true)->get();
        $divisions = Division::query()->where('is_active', true)->get();

        $query = KpiResult::query()
            ->with(['evaluatee.office', 'evaluatee.division', 'evaluatee.position', 'period'])
            ->when($selectedPeriodId, fn ($q) => $q->where('period_id', $selectedPeriodId));

        if ($request->filled('office_id')) {
            $query->whereHas('evaluatee', fn ($q) => $q->where('office_id', $request->query('office_id')));
        }

        if ($request->filled('division_id')) {
            $query->whereHas('evaluatee', fn ($q) => $q->where('division_id', $request->query('division_id')));
        }

        $results = $query->paginate(15);

        $assignments = KpiAssignment::query()
            ->with(['evaluator', 'evaluatee', 'period'])
            ->when($selectedPeriodId, fn ($q) => $q->where('period_id', $selectedPeriodId))
            ->latest()
            ->take(10)
            ->get();

        return view('kpi.index', compact('periods', 'selectedPeriodId', 'offices', 'divisions', 'results', 'assignments'));
    }

    /**
     * Export rekap KPI 360 ke file CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $selectedPeriodId = $request->query('period_id');

        $query = KpiResult::query()
            ->with(['evaluatee.office', 'evaluatee.division', 'evaluatee.position', 'period'])
            ->when($selectedPeriodId, fn ($q) => $q->where('period_id', $selectedPeriodId));

        if ($request->filled('office_id')) {
            $query->whereHas('evaluatee', fn ($q) => $q->where('office_id', $request->query('office_id')));
        }

        if ($request->filled('division_id')) {
            $query->whereHas('evaluatee', fn ($q) => $q->where('division_id', $request->query('division_id')));
        }

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="rekap_kpi_360_'.date('Ymd_His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['NIP', 'Nama Karyawan', 'Kantor', 'Divisi', 'Jabatan', 'Nilai P1', 'Nilai P2', 'Nilai P3', 'Nilai Akhir', 'Predikat']);

            $query->chunk(100, function ($records) use ($handle) {
                foreach ($records as $r) {
                    fputcsv($handle, [
                        $r->evaluatee?->nip ?? '-',
                        $r->evaluatee?->name ?? '-',
                        $r->evaluatee?->office?->name ?? '-',
                        $r->evaluatee?->division?->name ?? '-',
                        $r->evaluatee?->position?->name ?? '-',
                        number_format((float) $r->score_p1, 2),
                        number_format((float) $r->score_p2, 2),
                        number_format((float) $r->score_p3, 2),
                        number_format((float) $r->final_score, 2),
                        $r->predicate,
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
