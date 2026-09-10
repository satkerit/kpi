<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Services\KpiCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EvaluationResultController extends Controller
{
    /**
     * Hitung nilai akhir KPI (Skinny Controller).
     */
    public function store(Request $request, KpiCalculationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'evaluatee_id' => ['required', 'integer', 'exists:employees,id'],
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
        ]);

        $service->processFinalScore((int) $validated['evaluatee_id'], (int) $validated['period_id']);

        return back()->with('success', 'Nilai KPI berhasil dihitung dan disimpan.');
    }

    /**
     * Hitung nilai akhir untuk seluruh pegawai yang punya penilaian
     * submitted pada periode yang sama (bulk dari halaman laporan).
     */
    public function calculateAll(Request $request, KpiCalculationService $service): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
        ]);

        $periodId = (int) $validated['period_id'];

        $evaluateeIds = KpiAssignment::query()
            ->where('period_id', $periodId)
            ->where('status', 'submitted')
            ->distinct()
            ->pluck('evaluatee_id');

        $count = 0;
        foreach ($evaluateeIds as $evaluateeId) {
            $service->processFinalScore((int) $evaluateeId, $periodId);
            $count++;
        }

        return redirect()->route('kpi.report', ['period_id' => $periodId])
            ->with('success', "Nilai KPI {$count} pegawai berhasil dihitung dan disimpan.");
    }
}
