@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Penugasan</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Penugasan Penilai KPI 360°</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola hubungan evaluasi antara penilai dan yang dinilai lintas kantor, divisi, & tipe (P1/P2/P3).</p>
        </div>
        <div class="bg-white rounded-xl px-4 py-2.5 shadow-card border border-slate-100 flex items-center gap-3 text-xs">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-navy"></span>
            <span class="font-medium text-ink-soft">SK Direksi No. 016/SK-Dir/BSB.02/XII/2024</span>
        </div>
    </div>

    {{-- Toolbar – Period filter & generate buttons --}}
    <div class="flex flex-wrap gap-3 mb-6 items-center">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm font-medium text-ink-soft">Periode</label>
            <select name="period_id" class="border rounded px-3 py-2" onchange="this.form.submit()">
                @foreach($periods as $p)
                    <option value="{{ $p->id }}" @selected($selectedPeriodId == $p->id)>{{ $p->name }}</option>
                @endforeach
            </select>
        </form>
        <form method="POST" action="{{ route('assignments.generate') }}" class="inline">
            @csrf
            <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
            <button class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition text-sm">Generate P1 & P3 (Hierarki)</button>
        </form>
        <form method="POST" action="{{ route('assignments.generatePeers') }}" class="inline">
            @csrf
            <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
            <button class="bg-purple-600 text-white px-4 py-2 rounded shadow hover:bg-purple-700 transition text-sm">Generate P2 (Rekan)</button>
        </form>
    </div>

    {{-- Manual assign card --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6 mb-8">
        <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Penugasan Manual
        </h2>
        <form method="POST" action="{{ route('assignments.assign') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            @csrf
            <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Pegawai yang Dinilai</label>
                <select name="evaluatee_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} — {{ $e->office?->name }} / {{ $e->division?->name }} (Level {{ $e->position?->level }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Penilai</label>
                <select name="evaluator_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    @foreach($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} — {{ $e->office?->name }} / {{ $e->division?->name }} (Level {{ $e->position?->level }})</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Tipe Penilai</label>
                <select name="evaluator_type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    <option value="P1">P1 (Atasan)</option>
                    <option value="P2">P2 (Rekan)</option>
                    <option value="P3">P3 (Bawahan)</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="w-full py-2 bg-navy text-white text-sm font-bold rounded-xl shadow hover:bg-navy-deep transition">
                    Simpan Penugasan
                </button>
            </div>
        </form>
    </div>

    {{-- Assignments table --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100 border-b border-slate-200 text-ink-muted">
                    <th class="px-4 py-3">Pegawai yang Dinilai</th>
                    <th class="px-4 py-3">Penilai</th>
                    <th class="px-4 py-3 text-center">Tipe</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($assignments as $a)
                    <tr class="hover:bg-slate-50/60 transition">
                        <td class="px-4 py-2">{{ $a->evaluatee->name ?? $a->evaluatee_id }}</td>
                        <td class="px-4 py-2">{{ $a->evaluator->name ?? $a->evaluator_id }}</td>
                        <td class="px-4 py-2 text-center">{{ $a->evaluator_type }}</td>
                        <td class="px-4 py-2 text-center">{{ $a->status }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('evaluations.form', $a) }}" class="text-blue-600 font-medium hover:underline">Form</a>
                            @if($a->status === 'pending')
                                <form method="POST" action="{{ route('assignments.destroy', $a) }}" class="inline" onsubmit="return confirm('Hapus penugasan?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-sm font-medium ml-2 hover:underline">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-ink-muted">Belum ada penugasan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-200 bg-slate-50 text-right">
            {{ $assignments->links() }}
        </div>
    </div>
</main>
@endsection
