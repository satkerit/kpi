<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\Evaluation\Strategies\StrategyFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AssignmentController extends Controller
{
    /**
     * Tugaskan penilai untuk seorang pegawai dengan validasi relasi 1 tingkat.
     */
    public function assign(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'period_id' => ['required', 'integer', 'exists:kpi_periods,id'],
            'evaluatee_id' => ['required', 'integer', 'exists:employees,id'],
            'evaluator_id' => ['required', 'integer', 'exists:employees,id', 'different:evaluatee_id'],
            'evaluator_type' => ['required', 'string', 'in:P1,P2,P3,SELF'],
        ]);

        $strategy = StrategyFactory::make($validated['evaluator_type']);
        $isValidRelationship = $strategy->validateRelationship(
            (int) $validated['evaluator_id'],
            (int) $validated['evaluatee_id']
        );

        if (! $isValidRelationship) {
            return back()->withErrors([
                'evaluator_id' => "Hubungan antara penilai dan yang dinilai tidak memenuhi kriteria {$validated['evaluator_type']} (maksimal 1 tingkat langsung).",
            ]);
        }

        KpiAssignment::query()->firstOrCreate([
            'period_id' => $validated['period_id'],
            'evaluatee_id' => $validated['evaluatee_id'],
            'evaluator_id' => $validated['evaluator_id'],
            'evaluator_type' => $validated['evaluator_type'],
        ], [
            'status' => 'pending',
        ]);

        return back()->with('success', "Penilai {$validated['evaluator_type']} berhasil ditugaskan.");
    }
}
