<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Domains\Evaluation\Models\KpiAssignment;
use App\Domains\HumanResource\Models\Division;
use App\Domains\HumanResource\Models\Employee;
use App\Domains\HumanResource\Models\Office;
use App\Domains\MasterKpi\Models\KpiPeriod;
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

        return view('kpi.report', compact('periods', 'selectedPeriodId', 'offices', 'divisions', 'employees', 'pendingCount'));
    }
}
