<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

        return redirect()->route('kpi.index')->with('success', 'Nilai KPI berhasil dihitung dan disimpan.');
    }
}
