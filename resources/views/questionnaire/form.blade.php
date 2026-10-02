@extends('layouts.questionnaire')

@section('title', 'Isi Kuesioner KPI 360°')
@section('period_label', $period->name . ' · Tahun ' . $period->year)
@section('evaluator', $me->name . ' (' . ($me->position?->name ?? 'Pegawai') . ')')

@php
    $total = count($sections);
    $done = collect($sections)->where(fn ($s) => $s['assignment']->status === 'submitted')->count();
    $progress = $total > 0 ? round($done / $total * 100) : 100;

    $typeTheme = [
        'P1' => [
            'grad_from' => 'from-blue-600',
            'grad_to' => 'to-sky-500',
            'ring' => 'ring-blue-200',
            'soft_bg' => 'bg-blue-50/80',
            'soft_border' => 'border-blue-200',
            'text_accent' => 'text-blue-700',
            'accent_fill' => 'accent-[#2563eb]',
            'tag_bg' => 'bg-blue-100 text-blue-800 border-blue-200',
            'sheet_label' => 'Sheet 2: Penilaian Atasan terhadap Bawahan (P1)',
            'desc' => 'Sebagai Atasan, Anda menilai bawahan langsung terkait Kedisiplinan, Keterampilan Teknis, dan Kepribadian.',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
        ],
        'P2' => [
            'grad_from' => 'from-purple-600',
            'grad_to' => 'to-fuchsia-500',
            'ring' => 'ring-purple-200',
            'soft_bg' => 'bg-purple-50/80',
            'soft_border' => 'border-purple-200',
            'text_accent' => 'text-purple-700',
            'accent_fill' => 'accent-[#9333ea]',
            'tag_bg' => 'bg-purple-100 text-purple-800 border-purple-200',
            'sheet_label' => 'Sheet 3: Penilaian Rekan Kerja 1 Atasan Langsung (P2)',
            'desc' => 'Sebagai Rekan Sejawat, Anda menilai teman kerja satu atasan terkait Keterampilan Teknis dan Kepribadian.',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 100-8 4 4 0 000 8zm-6-2a3 3 0 100-6 3 3 0 000 6z"></path></svg>',
        ],
        'P3' => [
            'grad_from' => 'from-amber-600',
            'grad_to' => 'to-orange-500',
            'ring' => 'ring-amber-200',
            'soft_bg' => 'bg-amber-50/80',
            'soft_border' => 'border-amber-200',
            'text_accent' => 'text-amber-700',
            'accent_fill' => 'accent-[#d97706]',
            'tag_bg' => 'bg-amber-100 text-amber-800 border-amber-200',
            'sheet_label' => 'Sheet 4: Penilaian Bawahan terhadap Atasan (P3)',
            'desc' => 'Sebagai Bawahan, Anda menilai Atasan Langsung / Manajer terkait Kepribadian & Kepemimpinan.',
            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118L2.57 10.1c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.52-4.674z"></path></svg>',
        ],
    ];

    $maxS = (float) $maxScore;
    $maxDec = rtrim(rtrim(number_format($maxS, 2), '0'), '.');
@endphp
@section('progress', (string) $progress)

@section('content')
    <div class="sticky top-0 z-30 backdrop-blur-xl bg-white/80 border-b border-slate-200/70 shadow-[0_4px_24px_-12px_rgba(15,23,42,0.15)]">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-[#0f2438] via-[#16324f] to-[#b98e1f] text-white grid place-items-center shadow-lg shadow-[#16324f]/20 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500 leading-none mb-1">KPI 360° · {{ $period->name }} {{ $period->year }}</p>
                        <h1 class="text-sm sm:text-base font-extrabold text-slate-900 tracking-tight truncate">
                            <span class="truncate">{{ $me->name }}</span>
                            <span class="text-slate-400 font-semibold text-xs">· {{ $me->position?->name ?? 'Pegawai' }}</span>
                        </h1>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right leading-none">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-1">Progress</p>
                        <p class="text-sm font-black text-slate-900"><span class="num">{{ $done }}</span>/<span class="num">{{ $total }}</span></p>
                    </div>
                    <div class="relative w-16 h-16 shrink-0" data-global-progress>
                        <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="#e2e8f0" stroke-width="3.2"></circle>
                            <circle cx="18" cy="18" r="15.9155" fill="none" stroke="url(#gpGrad)" stroke-width="3.2" stroke-linecap="round" stroke-dasharray="{{ $progress }}, 100" data-global-progress-ring></circle>
                            <defs>
                                <linearGradient id="gpGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="#0f2438"/>
                                    <stop offset="100%" stop-color="#b98e1f"/>
                                </linearGradient>
                            </defs>
                        </svg>
                        <div class="absolute inset-0 grid place-items-center text-xs font-black text-slate-900 num" data-global-progress-label>{{ $progress }}%</div>
                    </div>
                </div>
            </div>
            <div class="mt-3 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-full bg-gradient-to-r from-[#0f2438] via-[#16324f] to-[#b98e1f] rounded-full transition-[width] duration-700 ease-out" style="width: {{ $progress }}%;" data-global-progress-bar></div>
            </div>
        </div>
    </div>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-6 pb-32">
        @if(session('success'))
            <div role="status" class="relative rounded-3xl p-[1px] mb-5 bg-gradient-to-r from-emerald-500 via-emerald-400 to-teal-400 shadow-xl shadow-emerald-500/15">
                <div class="rounded-[calc(1.5rem-1px)] bg-white px-5 py-4 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-100 text-emerald-600 grid place-items-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <div class="flex-1 pt-0.5">
                        <p class="text-sm font-extrabold text-slate-900">Berhasil Disimpan!</p>
                        <p class="text-xs text-slate-600 mt-0.5">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif
        @if(session('error'))
            <div role="alert" class="relative rounded-3xl p-[1px] mb-5 bg-gradient-to-r from-rose-500 via-rose-400 to-red-400 shadow-xl shadow-rose-500/15">
                <div class="rounded-[calc(1.5rem-1px)] bg-white px-5 py-4 flex items-start gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-100 text-rose-600 grid place-items-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div class="flex-1 pt-0.5">
                        <p class="text-sm font-extrabold text-slate-900">Perhatian</p>
                        <p class="text-xs text-slate-600 mt-0.5">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Bento header & scale legend --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <div class="lg:col-span-2 relative rounded-3xl p-[1px] bg-gradient-to-br from-[#0f2438] via-[#16324f] to-[#b98e1f] overflow-hidden shadow-2xl shadow-[#0f2438]/20">
                <div class="rounded-[calc(1.5rem-1px)] bg-white p-6 sm:p-7 relative overflow-hidden">
                    <div class="absolute -top-24 -right-20 w-60 h-60 rounded-full bg-gradient-to-br from-[#b98e1f]/20 to-transparent blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-60 h-60 rounded-full bg-gradient-to-tr from-[#16324f]/10 to-transparent blur-3xl pointer-events-none"></div>
                    <div class="relative">
                        <div class="inline-flex items-center gap-2 rounded-full bg-[#f6ecd4] border border-[#b98e1f]/30 text-[#8a6a12] px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#b98e1f] animate-pulse"></span>
                            SK Direksi No 016
                        </div>
                        <h2 class="mt-3 text-2xl sm:text-3xl font-black text-slate-900 tracking-tight leading-tight">
                            Formulir Penilaian <span class="bg-gradient-to-r from-[#0f2438] to-[#b98e1f] bg-clip-text text-transparent">KPI 360 Derajat</span>
                        </h2>
                        <p class="mt-2 text-sm text-slate-600 leading-relaxed max-w-xl">
                            Evaluasi <strong class="text-slate-900">{{ $total }} orang</strong> rekan kerja berdasarkan
                            perspektif objektif Anda. Isi setiap indikator dengan jujur demi kemajuan tim.
                        </p>
                        <div class="mt-5 grid grid-cols-3 gap-3">
                            <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-3.5 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Total Target</p>
                                <p class="mt-1 text-2xl font-black text-slate-900 num">{{ $total }}</p>
                            </div>
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/60 p-3.5 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-600">Selesai</p>
                                <p class="mt-1 text-2xl font-black text-emerald-700 num">{{ $done }}</p>
                            </div>
                            <div class="rounded-2xl border border-amber-200 bg-amber-50/60 p-3.5 text-center">
                                <p class="text-[10px] font-bold uppercase tracking-wider text-amber-600">Sisa</p>
                                <p class="mt-1 text-2xl font-black text-amber-700 num">{{ $pendingCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl bg-white border border-slate-200/80 shadow-card p-5 flex flex-col gap-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-slate-900 text-white grid place-items-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                        </div>
                        <p class="text-sm font-extrabold text-slate-900">Skala Penilaian</p>
                    </div>
                    <span class="text-[10px] font-mono font-bold text-slate-400">0 → {{ $maxDec }}</span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-rose-50/60 transition">
                        <span class="w-3 h-3 rounded-full bg-gradient-to-br from-rose-500 to-red-500 shadow-sm shadow-rose-500/40"></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-extrabold text-slate-900">0 – 3.5 <span class="font-semibold text-rose-700">Sangat Buruk</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-orange-50/60 transition">
                        <span class="w-3 h-3 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 shadow-sm shadow-orange-500/40"></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-extrabold text-slate-900">3.51 – 5.5 <span class="font-semibold text-orange-700">Buruk</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-amber-50/60 transition">
                        <span class="w-3 h-3 rounded-full bg-gradient-to-br from-amber-500 to-yellow-500 shadow-sm shadow-amber-500/40"></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-extrabold text-slate-900">5.51 – 7.5 <span class="font-semibold text-amber-700">Cukup Baik</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-sky-50/60 transition">
                        <span class="w-3 h-3 rounded-full bg-gradient-to-br from-sky-500 to-blue-500 shadow-sm shadow-sky-500/40"></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-extrabold text-slate-900">7.51 – 9.5 <span class="font-semibold text-sky-700">Baik</span></p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2.5 p-2 rounded-xl hover:bg-emerald-50/60 transition">
                        <span class="w-3 h-3 rounded-full bg-gradient-to-br from-emerald-500 to-teal-500 shadow-sm shadow-emerald-500/40"></span>
                        <div class="flex-1">
                            <p class="text-[11px] font-extrabold text-slate-900">9.51 – {{ $maxDec }} <span class="font-semibold text-emerald-700">Sangat Baik</span></p>
                        </div>
                    </div>
                </div>
                <div class="mt-auto pt-2 border-t border-slate-100 text-[10px] text-slate-500 leading-relaxed">
                    💡 <span class="font-semibold text-slate-600">Tips:</span> Geser slider atau klik cepat pada angka preset untuk mengisi nilai.
                </div>
            </div>
        </div>

        @if($pendingCount > 0)
            <form method="POST" action="{{ route('questionnaire.submitAll') }}" id="kpiQuestionnaireForm" novalidate>
                @csrf

                @foreach($sections as $i => $s)
                    @php
                        $a = $s['assignment'];
                        $submitted = $a->status === 'submitted';
                        $theme = $typeTheme[$a->evaluator_type] ?? $typeTheme['P1'];
                        $groupedSubcriteria = $s['subcriteria']->groupBy(function ($sc) {
                            return $sc->criteria?->name ?? 'Kriteria Penilaian';
                        });
                        $totalSc = $s['subcriteria']->count();
                    @endphp

                    <section id="section-{{ $a->form_key }}" data-section="{{ $a->form_key }}"
                             class="group rounded-3xl bg-white border border-slate-200/80 shadow-card mb-5 overflow-hidden transition-all duration-300 scroll-mt-28 hover:shadow-xl hover:shadow-slate-200/40 hover:-translate-y-0.5">
                        <div class="px-5 sm:px-6 py-4 {{ $submitted ? 'bg-emerald-50/60 border-emerald-200/60' : 'bg-white border-slate-200/80' }} border-b flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0">
                                <div class="w-11 h-11 rounded-2xl {{ $submitted ? 'bg-gradient-to-br from-emerald-500 to-teal-500 shadow-emerald-500/30' : 'bg-gradient-to-br ' . $theme['grad_from'] . ' ' . $theme['grad_to'] . ' shadow-lg shadow-slate-900/10' }} text-white grid place-items-center shrink-0 text-sm font-extrabold">
                                    @if($submitted)
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    @else
                                        {{ $i + 1 }}
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="font-black text-base sm:text-lg text-slate-900 tracking-tight truncate">{{ $a->evaluatee->name }}</h2>
                                        <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase text-white bg-gradient-to-r {{ $theme['grad_from'] }} {{ $theme['grad_to'] }} shadow-sm">
                                            {!! $theme['icon'] !!}
                                            <span>{{ $a->evaluator_type }}</span>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0"></path></svg> <span class="font-mono font-semibold">{{ $a->evaluatee->nik }}</span></span>
                                        <span class="text-slate-300">·</span>
                                        <span>{{ $a->evaluatee->position?->name ?? '—' }}</span>
                                        <span class="text-slate-300">·</span>
                                        <span>{{ $a->evaluatee->office?->name ?? '—' }}</span>
                                    </p>
                                    <div class="mt-2.5 flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-[10px] font-bold {{ $theme['tag_bg'] }} border">
                                            {!! $theme['icon'] !!}
                                            {{ $labels[$a->evaluator_type] ?? $a->evaluator_type }}
                                        </span>
                                        @if($submitted)
                                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                                Telah Disimpan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Perlu Diisi · {{ $totalSc }} indikator
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @unless($submitted)
                                <div class="shrink-0 flex flex-col items-end gap-2">
                                    <div class="flex flex-col items-end">
                                        <p class="text-[9px] font-bold uppercase tracking-wider text-slate-400">Terisi</p>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-lg font-black text-slate-900 num" data-section-filled>0</span>
                                            <span class="text-xs text-slate-400 font-semibold num">/{{ $totalSc }}</span>
                                        </div>
                                    </div>
                                    <div class="w-28 h-2 rounded-full bg-slate-100 overflow-hidden">
                                        <div class="h-full rounded-full bg-gradient-to-r {{ $theme['grad_from'] }} {{ $theme['grad_to'] }} transition-[width] duration-500 ease-out" style="width: 0%;" data-section-progress></div>
                                    </div>
                                    <button type="button" data-collapse class="w-8 h-8 rounded-xl border border-slate-200 text-slate-500 hover:text-slate-900 hover:border-slate-300 hover:bg-slate-50 transition grid place-items-center shrink-0" aria-label="Tampilkan/Sembunyikan form">
                                        <svg class="w-4 h-4 transition-transform duration-300" data-collapse-icon fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>
                                </div>
                            @endunless
                        </div>

                        <div class="px-5 sm:px-6 py-3.5 bg-gradient-to-r {{ $theme['soft_bg'] }} border-b {{ $theme['soft_border'] }} flex items-start gap-2.5">
                            <div class="w-6 h-6 rounded-lg bg-white border {{ $theme['soft_border'] }} grid place-items-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5 {{ $theme['text_accent'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <p class="text-xs text-slate-700 leading-relaxed">
                                <span class="font-bold text-slate-900">{{ $theme['sheet_label'] }}.</span>
                                {{ $theme['desc'] }}
                            </p>
                        </div>

                        @if($submitted)
                            <div class="p-5 sm:p-6 bg-slate-50/40">
                                <div class="rounded-2xl border border-slate-200 overflow-hidden bg-white">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-100/80 text-slate-700 font-bold text-xs border-b border-slate-200">
                                            <tr>
                                                <th class="px-4 py-3 text-left">Sub Kriteria Evaluasi</th>
                                                <th class="px-4 py-3 text-center w-32">Nilai</th>
                                                <th class="px-4 py-3 text-left hidden sm:table-cell">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($a->scores as $scScore)
                                                @php
                                                    $rs = (float) $scScore->raw_score;
                                                    if ($rs > 9.5) $rsCls = 'text-emerald-700 bg-emerald-50 border-emerald-200';
                                                    elseif ($rs > 7.5) $rsCls = 'text-sky-700 bg-sky-50 border-sky-200';
                                                    elseif ($rs > 5.5) $rsCls = 'text-amber-700 bg-amber-50 border-amber-200';
                                                    elseif ($rs > 3.5) $rsCls = 'text-orange-700 bg-orange-50 border-orange-200';
                                                    else $rsCls = 'text-rose-700 bg-rose-50 border-rose-200';
                                                @endphp
                                                <tr class="hover:bg-slate-50/60 transition">
                                                    <td class="px-4 py-3">
                                                        <div class="font-semibold text-slate-800 text-sm">{{ $scScore->subcriteria?->name ?? '—' }}</div>
                                                        <div class="text-[11px] text-slate-400 mt-0.5">Bobot <span class="font-mono">{{ $scScore->subcriteria?->weight ?? 0 }}%</span></div>
                                                        @if($scScore->comment)
                                                            <div class="sm:hidden text-[11px] text-slate-500 italic mt-1">“{{ Str::limit($scScore->comment, 60) }}”</div>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-center">
                                                        @if($scScore->raw_score === null)
                                                            <span class="inline-flex items-center rounded-lg border border-slate-200 bg-slate-100 text-slate-500 text-[11px] font-bold px-2 py-1">
                                                                TIDAK DIAMATI
                                                            </span>
                                                        @else
                                                            <span class="inline-flex items-center rounded-xl border px-2.5 py-1 text-sm font-black num {{ $rsCls }}">
                                                                {{ number_format($rs, 1) }}
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-4 py-3 text-slate-600 italic text-xs hidden sm:table-cell align-top">
                                                        {{ $scScore->comment ? '“'.$scScore->comment.'”' : '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="p-5 sm:p-6 space-y-5" data-section-body>
                                @php $itemIndex = 1; @endphp

                                @foreach($groupedSubcriteria as $criteriaName => $subList)
                                    <div class="rounded-2xl border border-slate-200 overflow-hidden bg-gradient-to-b from-white to-slate-50/40 shadow-sm">
                                        <div class="px-4 sm:px-5 py-3 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-2.5 min-w-0">
                                                <div class="w-8 h-8 rounded-xl bg-gradient-to-br {{ $theme['grad_from'] }} {{ $theme['grad_to'] }} text-white grid place-items-center shrink-0 shadow-sm">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <h3 class="font-extrabold text-xs sm:text-sm text-slate-900 uppercase tracking-wide truncate">{{ $criteriaName }}</h3>
                                                    <p class="text-[10px] text-slate-500 mt-0.5">{{ $subList->count() }} indikator · Bobot grup {{ rtrim(rtrim(number_format($subList->sum('weight'), 2), '0'), '.') }}%</p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="divide-y divide-slate-100">
                                            @foreach($subList as $sc)
                                                @php
                                                    $field = "scores.{$a->form_key}.{$sc->id}";
                                                    $oldVal = old("scores.{$a->form_key}.{$sc->id}");
                                                    $naChecked = old("not_observed.{$a->form_key}.{$sc->id}");
                                                    $oldComment = old("comments.{$a->form_key}.{$sc->id}");
                                                @endphp
                                                <div class="p-4 sm:p-5 hover:bg-white/80 transition" data-score-row data-sc="{{ $sc->id }}" data-theme="{{ $a->evaluator_type }}" data-max="{{ $maxS }}">
                                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="flex items-center gap-2 flex-wrap">
                                                                <span class="text-[10px] font-mono font-black text-slate-400 bg-slate-100 border border-slate-200 rounded px-2 py-0.5">#{{ $itemIndex++ }}</span>
                                                                <h4 class="text-sm font-extrabold text-slate-900 leading-snug">{{ $sc->name }}</h4>
                                                                <span class="inline-flex items-center rounded-lg px-2 py-0.5 text-[10px] font-bold {{ $theme['tag_bg'] }} border font-mono">
                                                                    Bobot {{ rtrim(rtrim(number_format((float) $sc->weight, 2), '0'), '.') }}%
                                                                </span>
                                                            </div>
                                                            @if($sc->description)
                                                                <p class="text-xs text-slate-600 mt-2 leading-relaxed rounded-xl bg-white border border-slate-200 p-2.5">
                                                                    {{ $sc->description }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                        <div class="shrink-0 sm:self-start flex items-center gap-2">
                                                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 hidden sm:block">Nilai</span>
                                                            <div class="w-16 h-12 rounded-2xl bg-slate-100 border border-slate-200 grid place-items-center transition-all duration-300" data-score-bubble>
                                                                <span class="text-slate-500 font-black text-base num">—</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 bg-white p-3 sm:p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)]">
                                                        <div class="space-y-3">
                                                            <div class="flex items-center gap-2.5 justify-between">
                                                                <span class="text-[10px] font-mono font-bold text-slate-400">0</span>
                                                                <div class="flex-1 flex items-center gap-1.5" data-preset-row>
                                                                    @php
                                                                        $presets = [];
                                                                        $stepCount = 5;
                                                                        for ($k = 0; $k <= $stepCount; $k++) {
                                                                            $presets[] = round(($maxS / $stepCount) * $k, 1);
                                                                        }
                                                                    @endphp
                                                                    @foreach($presets as $pv)
                                                                        <button type="button" data-preset="{{ $pv }}"
                                                                                class="flex-1 h-7 rounded-lg border border-slate-200 bg-slate-50 hover:bg-white hover:border-slate-300 text-[10px] font-mono font-bold text-slate-600 hover:text-slate-900 transition active:scale-95">
                                                                            {{ rtrim(rtrim(number_format($pv, 1), '0'), '.') }}
                                                                        </button>
                                                                    @endforeach
                                                                </div>
                                                                <span class="text-[10px] font-mono font-bold text-slate-400">{{ $maxDec }}</span>
                                                            </div>

                                                            <div class="flex items-center gap-3">
                                                                <div class="flex-1 relative h-10">
                                                                    <input type="range" min="0" max="{{ $maxS }}" step="0.5" value="{{ $oldVal ?? 0 }}" data-score-slider
                                                                           class="w-full h-2.5 absolute top-1/2 -translate-y-1/2 appearance-none bg-slate-200 rounded-full cursor-pointer outline-none z-10 {{ $theme['accent_fill'] }}"
                                                                           aria-label="Nilai {{ $sc->name }}">
                                                                    <div class="absolute top-1/2 left-0 -translate-y-1/2 h-2.5 rounded-full bg-gradient-to-r {{ $theme['grad_from'] }} {{ $theme['grad_to'] }} pointer-events-none transition-[width] duration-150 ease-out" data-slider-fill style="width: 0%;"></div>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 shrink-0">
                                                                    <div class="relative">
                                                                        <input type="number" name="scores[{{ $a->form_key }}][{{ $sc->id }}]" value="{{ $oldVal }}" min="0" max="{{ $maxS }}" step="0.5"
                                                                               placeholder="0-{{ $maxDec }}" data-score-input
                                                                               class="num w-24 border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-black text-slate-900 text-center bg-white focus:ring-4 focus:ring-slate-900/5 focus:border-slate-900 outline-none transition disabled:bg-slate-100 disabled:text-slate-400 disabled:border-slate-200">
                                                                        <span class="absolute pointer-events-none right-2 top-1/2 -translate-y-1/2 text-[10px] font-mono font-bold text-slate-300">/{{ $maxDec }}</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100">
                                                            <div class="flex items-center gap-3">
                                                                @if($allowNotObserved)
                                                                    <label class="group inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 cursor-pointer select-none">
                                                                        <span class="relative inline-flex items-center justify-center">
                                                                            <input type="checkbox" name="not_observed[{{ $a->form_key }}][{{ $sc->id }}]" value="1" @checked($naChecked) data-not-observed
                                                                                   class="peer sr-only">
                                                                            <span class="w-9 h-5 rounded-full bg-slate-200 transition-all peer-checked:bg-gradient-to-r peer-checked:{{ $theme['grad_from'] }} peer-checked:{{ $theme['grad_to'] }}"></span>
                                                                            <span class="absolute left-0.5 top-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-4"></span>
                                                                        </span>
                                                                        <span>Tidak Diamati <span class="text-slate-400 font-normal">· nilai diabaikan</span></span>
                                                                    </label>
                                                                @endif
                                                            </div>

                                                            <div class="inline-flex items-center" data-comment-wrapper>
                                                                <input type="checkbox" id="cmt-{{ $a->form_key }}-{{ $sc->id }}" class="peer sr-only" @checked((bool) $oldComment)>
                                                                <label for="cmt-{{ $a->form_key }}-{{ $sc->id }}"
                                                                       class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-[11px] font-bold {{ $theme['text_accent'] }} bg-white border border-slate-200 hover:border-current/50 hover:bg-slate-50 transition cursor-pointer">
                                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                                                    <span data-comment-label>Tambah Catatan</span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="mt-2 hidden" data-comment-box>
                                                            <div class="relative rounded-2xl border border-slate-200 focus-within:border-slate-400 focus-within:ring-4 focus-within:ring-slate-900/5 transition overflow-hidden">
                                                                <textarea name="comments[{{ $a->form_key }}][{{ $sc->id }}]" rows="2" maxlength="1000"
                                                                          placeholder="Tuliskan catatan atau feedback objektif mengenai indikator ini (opsional)."
                                                                          class="w-full text-xs sm:text-sm text-slate-800 p-3 pr-14 focus:outline-none resize-y min-h-[56px]">{{ $oldComment }}</textarea>
                                                                <div class="absolute bottom-2 right-3 text-[10px] font-mono text-slate-400">
                                                                    <span data-comment-count>0</span>/1000
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @error($field)
                                                            <p role="alert" class="mt-2 text-xs font-bold text-rose-600 flex items-center gap-1.5">
                                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                                {{ $message }}
                                                            </p>
                                                        @enderror
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endforeach
            </form>
        @else
            <div class="rounded-[2rem] p-[1px] bg-gradient-to-br from-emerald-500 via-teal-400 to-sky-500 shadow-2xl shadow-emerald-500/20 mx-auto max-w-2xl">
                <div class="rounded-[calc(2rem-1px)] bg-white px-6 py-12 sm:py-14 text-center relative overflow-hidden">
                    <div class="absolute -top-20 -right-20 w-56 h-56 rounded-full bg-gradient-to-br from-emerald-300/30 to-transparent blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-20 -left-20 w-56 h-56 rounded-full bg-gradient-to-tr from-sky-300/30 to-transparent blur-3xl pointer-events-none"></div>
                    <div class="relative">
                        <div class="mx-auto w-24 h-24 rounded-[1.75rem] bg-gradient-to-br from-emerald-400 via-emerald-500 to-teal-500 text-white grid place-items-center shadow-xl shadow-emerald-500/30 animate-[pop_.5s_ease-out]" style="animation-duration: .6s;">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h2 class="mt-6 text-3xl sm:text-4xl font-black text-slate-900 tracking-tight">Selesai! 🎉</h2>
                        <p class="mt-3 text-sm sm:text-base text-slate-600 max-w-md mx-auto leading-relaxed">
                            Terima kasih, <strong class="text-slate-900">{{ $me->name }}</strong>. Semua penilaian Anda telah tersimpan.
                            Kontribusi Anda sangat berarti untuk pengembangan tim &amp; perusahaan.
                        </p>
                        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                            <a href="{{ route('questionnaire.start') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#0f2438] to-[#16324f] text-white text-sm font-extrabold hover:from-[#16324f] hover:to-[#0f2438] transition shadow-lg shadow-[#0f2438]/20">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                Kembali ke Halaman Utama
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @if($pendingCount > 0)
        <div class="fixed inset-x-0 bottom-0 z-40 pointer-events-none">
            <div class="bg-gradient-to-t from-white via-white to-white/0 pt-10 pb-4 px-4 sm:px-6 pointer-events-auto">
                <div class="max-w-5xl mx-auto rounded-3xl p-[1px] bg-gradient-to-r from-[#0f2438] via-[#16324f] to-[#b98e1f] shadow-2xl shadow-[#0f2438]/30">
                    <div class="rounded-[calc(1.5rem-1px)] bg-white/95 backdrop-blur-xl px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3">
                        <div class="flex items-center gap-3 text-left min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-[#b98e1f] to-orange-500 text-white grid place-items-center shrink-0 shadow-md shadow-amber-500/30">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                                    Sisa Formulir
                                </p>
                                <p class="font-extrabold text-slate-900 text-sm sm:text-base leading-tight">
                                    <span class="num text-rose-600" data-remaining>{{ $pendingCount }}</span> penilaian belum lengkap
                                    <span class="hidden sm:inline text-slate-400 font-bold">·</span>
                                    <span class="text-xs text-slate-500 font-semibold block sm:inline" data-unfilled-hint>isi nilai atau tandai Tidak Diamati</span>
                                </p>
                            </div>
                        </div>
                        <button type="button" id="submitBtn"
                                class="w-full sm:w-auto shrink-0 group inline-flex items-center justify-center gap-2 px-6 sm:px-8 py-3 rounded-2xl bg-gradient-to-r from-[#0f2438] via-[#16324f] to-[#b98e1f] text-white font-extrabold shadow-lg shadow-[#0f2438]/30 hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0 active:scale-[.99] transition-all">
                            <span class="text-sm">Kirim Semua Penilaian</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Confirm Modal --}}
        <div id="confirmModal" class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-modal-close></div>
            <div class="relative w-full max-w-md rounded-3xl bg-white shadow-2xl animate-[slideUp_.3s_ease-out]">
                <div class="p-6 sm:p-7">
                    <div class="flex items-start gap-3.5">
                        <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-600 grid place-items-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h3 id="confirmTitle" class="text-lg font-black text-slate-900 tracking-tight">Kirim Semua Penilaian?</h3>
                            <p class="text-sm text-slate-600 mt-1.5 leading-relaxed">
                                Setelah dikirim, <strong class="text-slate-900">semua nilai akan terkunci</strong> dan tidak dapat diubah demi kerahasiaan &amp; integritas evaluasi.
                            </p>
                            <div id="confirmWarn" class="mt-3 rounded-2xl bg-rose-50 border border-rose-200 p-3 text-xs text-rose-700 font-semibold hidden">
                                ⚠️ <span id="confirmWarnText"></span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5">
                        <button type="button" data-modal-close
                                class="px-5 py-2.5 rounded-2xl text-sm font-extrabold text-slate-700 hover:bg-slate-100 transition">
                            Batal, Cek Lagi
                        </button>
                        <button type="button" id="confirmSubmit"
                                class="px-5 py-2.5 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 text-white text-sm font-extrabold hover:from-emerald-700 hover:to-teal-700 shadow-lg shadow-emerald-500/20 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            Ya, Kirim Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pop {
            0% { transform: scale(.8); opacity: 0; }
            60% { transform: scale(1.08); }
            100% { transform: scale(1); opacity: 1; }
        }
        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            background: #fff;
            border: 2.5px solid #0f2438;
            cursor: pointer;
            box-shadow: 0 4px 10px -4px rgba(15,36,56,0.4);
            transition: transform .15s ease;
        }
        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.15);
        }
        input[type="range"]::-moz-range-thumb {
            width: 20px;
            height: 20px;
            border-radius: 9999px;
            background: #fff;
            border: 2.5px solid #0f2438;
            cursor: pointer;
            box-shadow: 0 4px 10px -4px rgba(15,36,56,0.4);
        }
    </style>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var THEMES = {
        P1: { from: '#2563eb', to: '#0ea5e9' },
        P2: { from: '#9333ea', to: '#d946ef' },
        P3: { from: '#d97706', to: '#f97316' },
    };

    function scoreColor(v, max) {
        var r = (v / max);
        if (v === '' || v === null || isNaN(v)) {
            return { bubble: 'bg-slate-100 border-slate-200 text-slate-500' };
        }
        if (r > 0.95) return { bubble: 'bg-gradient-to-br from-emerald-500 to-teal-500 border-transparent text-white ring-4 ring-emerald-100' };
        if (r > 0.75) return { bubble: 'bg-gradient-to-br from-sky-500 to-blue-500 border-transparent text-white ring-4 ring-sky-100' };
        if (r > 0.55) return { bubble: 'bg-gradient-to-br from-amber-500 to-yellow-400 border-transparent text-white ring-4 ring-amber-100' };
        if (r > 0.35) return { bubble: 'bg-gradient-to-br from-orange-500 to-amber-500 border-transparent text-white ring-4 ring-orange-100' };
        return { bubble: 'bg-gradient-to-br from-rose-500 to-red-500 border-transparent text-white ring-4 ring-rose-100' };
    }

    function setBubbleClasses(el, classes) {
        el.removeAttribute('class');
        el.className = 'w-16 h-12 rounded-2xl grid place-items-center transition-all duration-300 border shadow-sm ' + classes;
    }

    function updateRow(row) {
        var slider = row.querySelector('[data-score-slider]');
        var number = row.querySelector('[data-score-input]');
        var bubble = row.querySelector('[data-score-bubble]');
        var na = row.querySelector('[data-not-observed]');
        var fill = row.querySelector('[data-slider-fill]');
        var presetBtns = row.querySelectorAll('[data-preset]');
        var max = parseFloat(row.getAttribute('data-max')) || 10;

        var val = '';
        if (na && na.checked) {
            bubble.innerHTML = '<span class="text-xs font-black tracking-wide">N/A</span>';
            setBubbleClasses(bubble, 'bg-slate-200 border-slate-300 text-slate-500');
            number.disabled = true;
            if (slider) slider.disabled = true;
            row.classList.add('opacity-60');
            if (fill) fill.style.width = '0%';
            presetBtns.forEach(function (b) { b.classList.add('opacity-40', 'pointer-events-none'); });
            number.value = '';
            return;
        } else {
            number.disabled = false;
            if (slider) slider.disabled = false;
            row.classList.remove('opacity-60');
            presetBtns.forEach(function (b) { b.classList.remove('opacity-40', 'pointer-events-none'); });
        }

        val = number.value;
        if (val === '' || val === null || isNaN(parseFloat(val))) {
            bubble.innerHTML = '<span class="font-black text-base num">—</span>';
            setBubbleClasses(bubble, scoreColor('', max).bubble);
            if (fill) fill.style.width = '0%';
            presetBtns.forEach(function (b) { b.classList.remove('bg-gradient-to-br', 'from-slate-900', 'to-slate-700', 'text-white', 'border-transparent'); });
            return;
        }

        var v = parseFloat(val);
        if (v < 0) v = 0;
        if (v > max) v = max;
        bubble.innerHTML = '<span class="font-black text-base num">' + v.toFixed(1) + '</span>';
        setBubbleClasses(bubble, scoreColor(v, max).bubble);
        if (fill) fill.style.width = Math.min(100, (v / max) * 100).toFixed(1) + '%';
        if (slider && parseFloat(slider.value) !== v) slider.value = String(v);

        var closest = null, diff = Infinity;
        presetBtns.forEach(function (b) {
            var pv = parseFloat(b.getAttribute('data-preset'));
            var d = Math.abs(pv - v);
            b.classList.remove('bg-gradient-to-br', 'from-slate-900', 'to-slate-700', 'text-white', 'border-transparent');
            b.classList.add('bg-slate-50', 'border-slate-200', 'text-slate-600');
            if (d < diff) { diff = d; closest = b; }
        });
        if (diff <= 0.25 && closest) {
            closest.classList.remove('bg-slate-50', 'border-slate-200', 'text-slate-600');
            closest.classList.add('bg-gradient-to-br', 'from-slate-900', 'to-slate-700', 'text-white', 'border-transparent', 'shadow-sm');
        }
    }

    function updateSection(section) {
        var rows = section.querySelectorAll('[data-score-row]');
        if (!rows.length) return;
        var filled = 0;
        rows.forEach(function (r) {
            var na = r.querySelector('[data-not-observed]');
            var ni = r.querySelector('[data-score-input]');
            var has = (na && na.checked) || (ni && ni.value !== '' && ni.value !== null && !isNaN(parseFloat(ni.value)));
            if (has) filled++;
        });
        var total = rows.length;
        var pct = total ? Math.round((filled / total) * 100) : 0;
        var bar = section.querySelector('[data-section-progress]');
        var lbl = section.querySelector('[data-section-filled]');
        if (bar) bar.style.width = pct + '%';
        if (lbl) lbl.textContent = String(filled);
        section.setAttribute('data-filled', String(filled));
        section.setAttribute('data-total', String(total));
        updateGlobal();
    }

    function updateGlobal() {
        var sections = document.querySelectorAll('[data-section][data-filled]');
        var filled = 0, total = 0;
        sections.forEach(function (s) {
            filled += parseInt(s.getAttribute('data-filled') || '0', 10);
            total += parseInt(s.getAttribute('data-total') || '0', 10);
        });
        var submitted = document.querySelectorAll('[data-section]').length - sections.length;
        var lbl = document.querySelector('[data-global-progress-label]');
        var ring = document.querySelector('[data-global-progress-ring]');
        var bar = document.querySelector('[data-global-progress-bar]');
        var secTotal = document.querySelectorAll('[data-section]').length;
        var pct2 = secTotal ? Math.round(((submitted + (sections.length ? (filled / Math.max(total, 1)) * sections.length : 0)) / secTotal) * 100) : 100;
        if (lbl) lbl.textContent = pct2 + '%';
        if (ring) ring.setAttribute('stroke-dasharray', pct2 + ', 100');
        if (bar) bar.style.width = pct2 + '%';

        var remaining = 0;
        sections.forEach(function (s) {
            var sf = parseInt(s.getAttribute('data-filled') || '0', 10);
            var st = parseInt(s.getAttribute('data-total') || '0', 10);
            if (sf < st) remaining++;
        });
        var el = document.querySelector('[data-remaining]');
        if (el) el.textContent = String(remaining);
    }

    document.querySelectorAll('[data-score-row]').forEach(function (row) {
        var slider = row.querySelector('[data-score-slider]');
        var number = row.querySelector('[data-score-input]');
        var na = row.querySelector('[data-not-observed]');
        var theme = row.getAttribute('data-theme');

        if (slider) {
            var t = THEMES[theme] || THEMES.P1;
            var th = slider.parentElement;
            if (th) {
                slider.style.background = '';
            }
        }

        var cmtToggle = row.querySelector('#cmt-' + row.closest('[data-section]').getAttribute('data-section') + '-' + row.getAttribute('data-sc'));
        var cmtBox = row.querySelector('[data-comment-box]');
        var cmtLabel = row.querySelector('[data-comment-label]');
        var cmtText = row.querySelector('textarea[name*="comments"]');
        var cmtCount = row.querySelector('[data-comment-count]');
        if (cmtToggle && cmtBox) {
            function syncCmt() {
                if (cmtToggle.checked) {
                    cmtBox.classList.remove('hidden');
                    if (cmtLabel) cmtLabel.textContent = 'Sembunyikan Catatan';
                } else {
                    cmtBox.classList.add('hidden');
                    if (cmtLabel) cmtLabel.textContent = 'Tambah Catatan';
                }
            }
            cmtToggle.addEventListener('change', syncCmt);
            syncCmt();
        }
        if (cmtText && cmtCount) {
            cmtCount.textContent = String(cmtText.value.length);
            cmtText.addEventListener('input', function () {
                cmtCount.textContent = String(cmtText.value.length);
            });
        }

        if (slider) {
            slider.addEventListener('input', function () {
                if (na && na.checked) { na.checked = false; }
                number.value = slider.value;
                updateRow(row);
                updateSection(row.closest('[data-section]'));
            });
        }
        if (number) {
            number.addEventListener('input', function () {
                if (na && na.checked) { na.checked = false; updateRow(row); }
                updateRow(row);
                updateSection(row.closest('[data-section]'));
            });
        }
        if (na) {
            na.addEventListener('change', function () {
                updateRow(row);
                updateSection(row.closest('[data-section]'));
            });
        }

        row.querySelectorAll('[data-preset]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var pv = btn.getAttribute('data-preset');
                if (na && na.checked) { na.checked = false; }
                number.value = pv;
                if (slider) slider.value = pv;
                updateRow(row);
                updateSection(row.closest('[data-section]'));
            });
        });

        updateRow(row);
    });

    document.querySelectorAll('[data-section]').forEach(function (sec) {
        updateSection(sec);
        var collapse = sec.querySelector('[data-collapse]');
        var body = sec.querySelector('[data-section-body]');
        var icon = sec.querySelector('[data-collapse-icon]');
        if (collapse && body && icon) {
            collapse.addEventListener('click', function () {
                var hidden = body.classList.toggle('hidden');
                icon.style.transform = hidden ? 'rotate(180deg)' : 'rotate(0deg)';
            });
        }
    });
    updateGlobal();

    var form = document.getElementById('kpiQuestionnaireForm');
    var submitBtn = document.getElementById('submitBtn');
    var modal = document.getElementById('confirmModal');
    var confirmSubmit = document.getElementById('confirmSubmit');
    var warn = document.getElementById('confirmWarn');
    var warnText = document.getElementById('confirmWarnText');

    function openModal() {
        var sections = document.querySelectorAll('[data-section][data-filled]');
        var incomp = [];
        sections.forEach(function (s) {
            var sf = parseInt(s.getAttribute('data-filled') || '0', 10);
            var st = parseInt(s.getAttribute('data-total') || '0', 10);
            if (sf < st) {
                var name = s.querySelector('h2');
                incomp.push(name ? name.textContent.trim() : ('#' + (Array.prototype.indexOf.call(document.querySelectorAll('[data-section]'), s) + 1)));
            }
        });
        if (incomp.length && warn && warnText) {
            warn.classList.remove('hidden');
            warnText.textContent = incomp.length + ' penilaian masih belum lengkap: ' + incomp.slice(0, 3).join(', ') + (incomp.length > 3 ? ', dll.' : '') + '. Anda tetap dapat melanjutkan mengirim yang sudah terisi saja.';
        } else if (warn) {
            warn.classList.add('hidden');
        }
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }
    if (submitBtn) submitBtn.addEventListener('click', openModal);
    if (modal) {
        modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
        });
    }
    if (confirmSubmit && form) {
        confirmSubmit.addEventListener('click', function () {
            var btn = confirmSubmit;
            btn.disabled = true;
            btn.classList.add('opacity-80', 'cursor-not-allowed');
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Mengirim...';
            form.submit();
        });
    }
})();
</script>
@endpush
