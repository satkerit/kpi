@extends('layouts.app')

@section('title', 'Formulir Kuesioner KPI — '.$period->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-extrabold text-ink">Formulir Kuesioner KPI 360°</h1>
        <p class="text-sm text-ink-muted mt-1">Periode: <span class="font-semibold text-navy">{{ $period->name }}</span></p>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            {{ session('error') }}
        </div>
    @endif

    @forelse(['P3' => 'Penilaian Atasan Langsung', 'P2' => 'Penilaian Rekan Selevel', 'P1' => 'Penilaian Bawahan', 'SELF' => 'Penilaian Diri Sendiri'] as $type => $label)
        @if ($groups->has($type))
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-4">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg font-extrabold text-sm
                        {{ $type === 'P1' ? 'bg-indigo-100 text-indigo-700' : ($type === 'P2' ? 'bg-amber-100 text-amber-700' : ($type === 'SELF' ? 'bg-teal-100 text-teal-700' : 'bg-emerald-100 text-emerald-700')) }}">
                        {{ $type }}
                    </span>
                    <h2 class="text-base font-extrabold text-ink">{{ $label }}</h2>
                    <span class="text-xs text-ink-muted font-medium">({{ $groups[$type]->count() }} pegawai)</span>
                </div>

                <div class="space-y-4">
                    @foreach ($groups[$type] as $assignment)
                        @php
                            $done = $assignment->status === 'submitted';
                            $evaluatee = $assignment->evaluatee;
                        @endphp
                        <div class="bg-white border border-slate-100 rounded-2xl shadow-card p-4 sm:p-5">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-navy-tint flex items-center justify-center text-navy font-bold text-sm">
                                        {{ mb_strtoupper(mb_substr($evaluatee->name ?? '?', 0, 2)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-sm text-ink">{{ $evaluatee->name }}</p>
                                        <p class="text-xs text-ink-muted">{{ $evaluatee->position?->name ?? '—' }}
                                            @if ($evaluatee->office)
                                                · {{ $evaluatee->office->name }}
                                            @endif
                                            @if ($evaluatee->department)
                                                · {{ $evaluatee->department->name }}
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                @if ($done)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-full border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        Selesai
                                    </span>
                                @else
                                    <a href="{{ route('evaluations.form', $assignment->id) }}"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-navy text-white text-xs font-bold rounded-xl hover:bg-navy-deep transition">
                                        Isi Formulir
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @empty
        <div class="text-center py-16 text-ink-muted text-sm">
            Belum ada penugasan kuesioner untuk periode ini.
        </div>
    @endforelse

    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
        <form method="POST" action="{{ route('logout') }}" class="inline">
            @csrf
            <button type="submit" class="text-xs text-ink-muted hover:text-rose-600 font-semibold transition">
                Keluar dari Sesi Kuesioner
            </button>
        </form>
    </div>
</div>
@endsection
