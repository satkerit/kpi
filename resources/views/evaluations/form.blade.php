@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-4xl mx-auto w-full">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Form Penilaian</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $assignment->evaluator_type }} – {{ $assignment->evaluatee->name ?? $assignment->evaluatee_id }}</h1>
            <p class="text-sm text-ink-muted mt-1">
                Periode: <strong>{{ $assignment->period->name ?? '-' }}</strong> •
                Kantor: <strong>{{ $assignment->evaluatee->office->name ?? '-' }}</strong> •
                Divisi: <strong>{{ $assignment->evaluatee->division->name ?? '-' }}</strong>
            </p>
        </div>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-xs font-medium text-ink-soft hover:text-navy transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-4 sm:p-6">
        <div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl bg-navy-tint border border-navy/10 px-4 py-3 text-xs sm:text-sm text-navy">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
            <span>Rentang nilai setiap subkriteria: <strong class="font-mono">Min: 0</strong> s.d. <strong class="font-mono">Maks: {{ $maxScore }}</strong></span>
        </div>
        <form method="POST" action="{{ route('evaluations.submit', $assignment->id) }}" class="space-y-4 sm:space-y-6">
            @csrf
            @foreach($subcriteriaList as $sc)
                @php($existing = $assignment->scores->firstWhere('subcriteria_id', $sc->id))
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start border border-slate-100 bg-paper/50 p-4 sm:p-5 rounded-2xl">
                    <div class="md:col-span-2 min-w-0">
                        <label class="block text-sm font-bold text-ink">
                            {{ $sc->name }}
                            <span class="block sm:inline text-xs font-medium text-ink-muted">(Bobot: {{ $sc->weight }}%)</span>
                        </label>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-white border border-slate-200 text-ink-soft font-mono">Min: 0</span>
                            <span class="text-[11px] text-ink-muted">|</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-gold-soft border border-gold/30 text-gold-deep font-mono">Maks: {{ $maxScore }}</span>
                        </div>
                        <input type="number" name="scores[{{ $sc->id }}]" min="0" max="{{ $maxScore }}" step="0.1"
                            value="{{ $existing?->raw_score }}"
                            data-score-input="{{ $sc->id }}"
                            {{ ($existing?->raw_score === null && $allowNotObserved) ? '' : 'required' }}
                            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none disabled:bg-slate-100 disabled:text-slate-400">
                        @error('scores.' . $sc->id)
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                        @if($allowNotObserved)
                            <label class="mt-2 inline-flex items-center gap-2 text-xs text-ink-muted">
                                <input type="checkbox" name="not_observed[{{ $sc->id }}]" value="1"
                                    data-not-observed="{{ $sc->id }}"
                                    {{ $existing && $existing->raw_score === null ? 'checked' : '' }}
                                    class="rounded border-slate-300 text-navy focus:ring-navy">
                                Tidak Diamati
                            </label>
                        @endif
                        <textarea name="comments[{{ $sc->id }}]" rows="2" maxlength="1000"
                            placeholder="Umpan balik kualitatif (opsional)..."
                            class="mt-2 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none">{{ $existing?->comment }}</textarea>
                    </div>
                    <div class="text-center md:col-span-1">
                        <span class="inline-block px-3 py-1 text-xs font-bold bg-navy-tint text-navy rounded">{{ $sc->evaluator_type }}</span>
                    </div>
                </div>
            @endforeach
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ url()->previous() }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-ink-soft hover:bg-slate-50 transition">Batal</a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">Simpan & Submit</button>
            </div>
        </form>
    </div>
</main>
@endsection
