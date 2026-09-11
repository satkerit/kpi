@extends('layouts.questionnaire')

@section('title', 'Isi Kuesioner KPI 360° - SK Direksi No 016/SK-Dir/BSB.02/XII/2024')
@section('period_label', $period->name . ' · Tahun ' . $period->year)
@section('evaluator', $me->name . ' (' . ($me->position?->name ?? 'Pegawai') . ')')

@php
    $total = count($sections);
    $done = collect($sections)->where(fn ($s) => $s['assignment']->status === 'submitted')->count();
    $progress = $total > 0 ? round($done / $total * 100) : 100;

    // Badge & tema visual per sheet SK Excel:
    // P1 = Sheet 2 (Atasan menilai Bawahan)
    // P2 = Sheet 3 (Rekan menilai Rekan 1 Atasan)
    // P3 = Sheet 4 (Bawahan menilai Atasan/Manajer)
    $typeTheme = [
        'P1' => [
            'badge' => 'bg-blue-600 text-white shadow-sm',
            'light_bg' => 'bg-blue-50/60',
            'border' => 'border-blue-200',
            'text_accent' => 'text-blue-800',
            'sheet_label' => 'Sheet 2: Penilaian Atasan terhadap Bawahan (P1)',
            'desc' => 'Sebagai Atasan, Anda menilai bawahan langsung terkait Kedisiplinan, Keterampilan Teknis, dan Kepribadian.',
            'icon' => '👔',
        ],
        'P2' => [
            'badge' => 'bg-purple-600 text-white shadow-sm',
            'light_bg' => 'bg-purple-50/60',
            'border' => 'border-purple-200',
            'text_accent' => 'text-purple-800',
            'sheet_label' => 'Sheet 3: Penilaian Rekan Kerja 1 Atasan Langsung (P2)',
            'desc' => 'Sebagai Rekan Sejawat, Anda menilai teman kerja satu atasan terkait Keterampilan Teknis dan Kepribadian.',
            'icon' => '🤝',
        ],
        'P3' => [
            'badge' => 'bg-amber-600 text-white shadow-sm',
            'light_bg' => 'bg-amber-50/60',
            'border' => 'border-amber-200',
            'text_accent' => 'text-amber-800',
            'sheet_label' => 'Sheet 4: Penilaian Bawahan terhadap Atasan (P3)',
            'desc' => 'Sebagai Bawahan, Anda menilai Atasan Langsung / Manajer terkait Kepribadian & Kepemimpinan.',
            'icon' => '⭐',
        ],
    ];
@endphp
@section('progress', (string) $progress)

@section('content')
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-2xl px-5 py-4 text-sm font-semibold mb-6 shadow-sm flex items-center gap-3">
            <span class="text-xl">✅</span>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-300 text-rose-800 rounded-2xl px-5 py-4 text-sm font-semibold mb-6 shadow-sm flex items-center gap-3">
            <span class="text-xl">⚠️</span>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- Banner Utama Standar SK Direksi & Panduan Ringkas --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-200/80 p-5 sm:p-7 mb-6 relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-gold/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 uppercase tracking-wider mb-2">
                    <span class="w-2 h-2 rounded-full bg-gold"></span> Standar SK Direksi No 016/SK-Dir/BSB.02/XII/2024
                </div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Formulir Pengisian KPI 360 Derajat</h1>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl leading-relaxed">
                    Selamat datang, <strong class="text-slate-900">{{ $me->name }}</strong>. Anda memiliki total <strong class="text-navy font-bold">{{ $total }} orang</strong> yang perlu dievaluasi pada periode <span class="font-semibold">{{ $period->name }}</span>.
                </p>
            </div>
            <div class="flex flex-col sm:items-end gap-2 shrink-0">
                <div class="inline-flex items-center gap-2 rounded-xl bg-navy-tint border border-navy/10 px-4 py-2 text-xs font-bold text-navy shadow-sm">
                    <span>Skala Nilai:</span>
                    <span class="px-2 py-0.5 rounded bg-white font-mono text-slate-800 border border-slate-200">Min 0</span>
                    <span>s/d</span>
                    <span class="px-2 py-0.5 rounded bg-gold text-white font-mono shadow-xs">Maks {{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}</span>
                </div>
                <p class="text-xs text-slate-500 text-right"><strong class="text-emerald-700 font-bold font-mono">{{ $done }}</strong> dari <strong class="font-mono">{{ $total }}</strong> penilaian selesai</p>
            </div>
        </div>

        {{-- Panduan Skala Pengisian SK --}}
        <div class="mt-5 grid grid-cols-2 sm:grid-cols-5 gap-2 text-xs pt-4 border-t border-slate-100">
            <div class="bg-emerald-50/80 border border-emerald-200 p-2.5 rounded-xl">
                <span class="font-extrabold text-emerald-800 block">&gt; 9.5 - 10</span>
                <span class="text-[11px] font-medium text-emerald-700">Sangat Baik</span>
            </div>
            <div class="bg-sky-50/80 border border-sky-200 p-2.5 rounded-xl">
                <span class="font-extrabold text-sky-800 block">&gt; 7.5 - 9.5</span>
                <span class="text-[11px] font-medium text-sky-700">Baik</span>
            </div>
            <div class="bg-amber-50/80 border border-amber-200 p-2.5 rounded-xl">
                <span class="font-extrabold text-amber-800 block">&gt; 5.5 - 7.5</span>
                <span class="text-[11px] font-medium text-amber-700">Cukup Baik</span>
            </div>
            <div class="bg-orange-50/80 border border-orange-200 p-2.5 rounded-xl">
                <span class="font-extrabold text-orange-800 block">&gt; 3.5 - 5.5</span>
                <span class="text-[11px] font-medium text-orange-700">Buruk</span>
            </div>
            <div class="bg-rose-50/80 border border-rose-200 p-2.5 rounded-xl col-span-2 sm:col-span-1">
                <span class="font-extrabold text-rose-800 block">0 - 3.5</span>
                <span class="text-[11px] font-medium text-rose-700">Sangat Buruk</span>
            </div>
        </div>

        {{-- Navigasi Cepat Tab Pegawai --}}
        @if($total > 1)
            <div class="mt-5 pt-4 border-t border-slate-100">
                <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">Lompat Cepat ke Pegawai:</p>
                <nav class="flex flex-wrap gap-2">
                    @foreach($sections as $i => $s)
                        @php
                            $a = $s['assignment'];
                            $isDone = $a->status === 'submitted';
                            $t = $typeTheme[$a->evaluator_type] ?? $typeTheme['P1'];
                        @endphp
                        <a href="#section-{{ $a->form_key }}"
                           class="inline-flex items-center gap-2 rounded-xl border px-3 py-1.5 text-xs font-semibold transition {{ $isDone ? 'bg-emerald-50 border-emerald-300 text-emerald-800' : 'bg-white border-slate-200 text-slate-700 hover:border-navy hover:text-navy' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold {{ $isDone ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700' }}">{{ $i + 1 }}</span>
                            <span class="truncate max-w-[130px] sm:max-w-none">{{ $a->evaluatee->name }}</span>
                            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase {{ $isDone ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $a->evaluator_type }}</span>
                            @if($isDone)
                                <span class="text-emerald-600 font-bold">✓</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        @endif
    </div>

    @if($pendingCount > 0)
        <form method="POST" action="{{ route('questionnaire.submitAll') }}" id="kpiQuestionnaireForm">
            @csrf

            @foreach($sections as $i => $s)
                @php
                    $a = $s['assignment'];
                    $submitted = $a->status === 'submitted';
                    $theme = $typeTheme[$a->evaluator_type] ?? $typeTheme['P1'];

                    // Kelompokkan subkriteria berdasarkan Kategori Kriteria (Kedisiplinan, Keterampilan Teknis, Kepribadian)
                    // sesuai format Sheet 2, 3, dan 4 Excel Lampiran SK Direksi
                    $groupedSubcriteria = $s['subcriteria']->groupBy(function ($sc) {
                        return $sc->criteria?->name ?? 'Kriteria Penilaian';
                    });
                @endphp

                <section id="section-{{ $a->form_key }}" class="bg-white rounded-2xl shadow-card border border-slate-200 mb-7 overflow-hidden scroll-mt-24 transition">
                    {{-- Header Kartu Pegawai yang Dinilai --}}
                    <div class="px-5 sm:px-7 py-4.5 border-b border-slate-200 {{ $submitted ? 'bg-emerald-50/70' : 'bg-slate-50' }}">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <span class="w-9 h-9 rounded-xl {{ $submitted ? 'bg-emerald-600 text-white' : 'bg-navy text-white' }} flex items-center justify-center text-sm font-extrabold shrink-0 shadow-sm">
                                    {{ $i + 1 }}
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h2 class="font-black text-base sm:text-lg text-slate-900 truncate">{{ $a->evaluatee->name }}</h2>
                                        <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[11px] font-extrabold {{ $theme['badge'] }}">
                                            <span>{{ $theme['icon'] }}</span>
                                            <span>{{ $a->evaluator_type }}</span>
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 mt-0.5 truncate">
                                        NIK: <span class="font-mono font-semibold">{{ $a->evaluatee->nik }}</span> ·
                                        Jabatan: <span class="font-semibold">{{ $a->evaluatee->position?->name ?? '—' }}</span> ·
                                        Kantor: <span class="font-semibold">{{ $a->evaluatee->office?->name ?? '—' }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0 flex items-center gap-2">
                                @if($submitted)
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                        Telah Disimpan
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                        Perlu Diisi
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Keterangan Peran / Konteks Lembar SK --}}
                        <div class="mt-3 text-xs text-slate-700 flex items-start gap-1.5 pt-2 border-t border-slate-200/60">
                            <span class="font-extrabold tracking-wide uppercase text-[10px] bg-white/70 px-2 py-0.5 rounded border border-current shrink-0">
                                {{ $labels[$a->evaluator_type] ?? $a->evaluator_type }}
                            </span>
                            <span class="leading-relaxed">{{ $theme['desc'] }}</span>
                        </div>
                    </div>

                    @if($submitted)
                        {{-- Tampilan Read-Only bila sudah terkirim --}}
                        <div class="p-5 sm:p-6 bg-slate-50/50">
                            <div class="rounded-xl bg-white border border-slate-200 overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead class="bg-slate-100 text-slate-700 font-bold border-b border-slate-200">
                                        <tr>
                                            <th class="p-3 text-left">Sub Kriteria Evaluasi</th>
                                            <th class="p-3 text-center w-28">Nilai Input</th>
                                            <th class="p-3 text-center w-28">Catatan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($a->scores as $scScore)
                                            <tr>
                                                <td class="p-3 font-semibold text-slate-800">
                                                    {{ $scScore->subcriteria?->name ?? '—' }}
                                                    <span class="text-[11px] text-slate-400 font-normal">({{ $scScore->subcriteria?->weight }}%)</span>
                                                </td>
                                                <td class="p-3 text-center font-mono font-bold text-navy">
                                                    @if($scScore->raw_score === null)
                                                        <span class="text-slate-400 italic">Tidak Diamati</span>
                                                    @else
                                                        {{ number_format((float) $scScore->raw_score, 1) }}
                                                    @endif
                                                </td>
                                                <td class="p-3 text-slate-600 italic">{{ $scScore->comment ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        {{-- Form Pengisian Berkelompok Sesuai Kategori SK Excel --}}
                        <div class="p-5 sm:p-7 space-y-6">
                            @php $itemIndex = 1; @endphp

                            @foreach($groupedSubcriteria as $criteriaName => $subList)
                                <div class="rounded-xl border border-slate-200 overflow-hidden bg-white shadow-xs">
                                    {{-- Group Header: Kedisiplinan / Keterampilan Teknis / Kepribadian --}}
                                    <div class="bg-slate-100/90 px-4 py-2.5 border-b border-slate-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full bg-gold-deep"></span>
                                            <h3 class="font-extrabold text-xs sm:text-sm text-slate-800 uppercase tracking-wide">
                                                {{ $criteriaName }}
                                            </h3>
                                        </div>
                                        <span class="text-[11px] text-slate-500 font-medium font-mono">
                                            Total Bobot Grup: {{ rtrim(rtrim(number_format($subList->sum('weight'), 2), '0'), '.') }}%
                                        </span>
                                    </div>

                                    {{-- Daftar Subkriteria --}}
                                    <div class="divide-y divide-slate-100">
                                        @foreach($subList as $sc)
                                            @php
                                                $field = "scores.{$a->form_key}.{$sc->id}";
                                                $oldVal = old("scores.{$a->form_key}.{$sc->id}");
                                                $naChecked = old("not_observed.{$a->form_key}.{$sc->id}");
                                            @endphp
                                            <div class="p-4 sm:p-5 hover:bg-slate-50/50 transition" data-score-row>
                                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-3">
                                                    <div class="min-w-0 flex-1">
                                                        <div class="flex items-center gap-2 flex-wrap">
                                                            <span class="text-xs font-bold text-slate-500 font-mono">#{{ $itemIndex++ }}</span>
                                                            <h4 class="text-sm font-extrabold text-slate-900">{{ $sc->name }}</h4>
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 border border-amber-200 text-amber-800 font-mono">
                                                                Bobot {{ rtrim(rtrim(number_format((float) $sc->weight, 2), '0'), '.') }}%
                                                            </span>
                                                        </div>
                                                        @if($sc->description)
                                                            <p class="text-xs text-slate-600 mt-1 leading-relaxed bg-slate-50 p-2 rounded-lg border border-slate-100">
                                                                {{ $sc->description }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    {{-- Tampilan Skor Live Badge --}}
                                                    <div class="shrink-0 flex items-center gap-2 sm:self-start">
                                                        <span class="text-[11px] text-slate-400 font-medium uppercase">Nilai:</span>
                                                        <div class="w-14 h-10 rounded-xl bg-navy text-white flex items-center justify-center font-mono font-extrabold text-lg shadow-sm" data-score-value>
                                                            —
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Kontrol Input: Slider Interaktif + Numeric Box --}}
                                                <div class="bg-paper/70 p-3 rounded-xl border border-slate-200/80 flex flex-col sm:flex-row items-center gap-3">
                                                    <div class="flex-1 w-full flex items-center gap-3">
                                                        <span class="text-[10px] font-mono font-bold text-slate-400">0</span>
                                                        <input type="range" min="0" max="{{ $maxScore }}" step="0.5" value="0" data-score-slider
                                                               class="flex-1 w-full accent-[#16324f] h-2.5 bg-slate-200 rounded-lg cursor-pointer transition"
                                                               aria-label="Nilai {{ $sc->name }}">
                                                        <span class="text-[10px] font-mono font-bold text-slate-400">{{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}</span>
                                                    </div>

                                                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                                        <input type="number" name="scores[{{ $a->form_key }}][{{ $sc->id }}]" value="{{ $oldVal }}" min="0" max="{{ $maxScore }}" step="0.5"
                                                               placeholder="0 - {{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}" data-score-input
                                                               class="num w-24 border border-slate-300 rounded-lg px-3 py-1.5 text-sm font-extrabold text-navy text-center bg-white shadow-xs focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                                        <span class="text-xs text-slate-400 font-mono">/ {{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}</span>
                                                    </div>
                                                </div>

                                                {{-- Opsi Tidak Diamati & Kolom Komentar --}}
                                                <div class="mt-2.5 flex flex-wrap items-center justify-between gap-2 pt-1">
                                                    @if($allowNotObserved)
                                                        <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 cursor-pointer select-none">
                                                            <input type="checkbox" name="not_observed[{{ $a->form_key }}][{{ $sc->id }}]" value="1" @checked($naChecked) data-not-observed
                                                                   class="w-4 h-4 rounded text-navy border-slate-300 focus:ring-navy">
                                                            <span>Tandai "Tidak Diamati" <span class="text-slate-400 font-normal">(tidak diperhitungkan)</span></span>
                                                        </label>
                                                    @else
                                                        <div></div>
                                                    @endif

                                                    <details class="text-xs">
                                                        <summary class="font-bold text-gold-deep cursor-pointer hover:underline list-none inline-flex items-center gap-1">
                                                            <span>💬 Tambah Catatan Kualitatif</span>
                                                        </summary>
                                                        <div class="mt-2">
                                                            <textarea name="comments[{{ $a->form_key }}][{{ $sc->id }}]" rows="2" maxlength="1000"
                                                                      placeholder="Tuliskan catatan atau feedback khusus mengenai indikator ini..."
                                                                      class="w-full text-xs border border-slate-300 rounded-lg p-2.5 focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none">{{ old("comments.{$a->form_key}.{$sc->id}") }}</textarea>
                                                        </div>
                                                    </details>
                                                </div>

                                                @error($field)
                                                    <p class="mt-2 text-xs font-semibold text-rose-600 flex items-center gap-1">
                                                        <span>⚠️</span> {{ $message }}
                                                    </p>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach

            {{-- Floating Bottom Bar untuk Submit --}}
            <div class="h-28"></div>
            <div class="fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-300 shadow-[0_-8px_25px_rgba(15,36,56,.12)]">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3 text-xs sm:text-sm text-slate-700">
                        <div class="w-9 h-9 rounded-xl bg-gold-soft text-gold-deep flex items-center justify-center font-bold text-base shrink-0">
                            📋
                        </div>
                        <div>
                            <p class="font-extrabold text-slate-900">
                                Sisa <span class="num text-rose-600">{{ $pendingCount }}</span> Formulir Pegawai yang Belum Dikirim
                            </p>
                            <p class="text-xs text-slate-500">Pastikan seluruh indikator terisi atau dicentang sesuai penilaian objektif Anda.</p>
                        </div>
                    </div>
                    <button type="submit" onclick="return confirm('Kirim seluruh lembar penilaian KPI 360 derajat sekarang? Data yang sudah disubmit akan terkunci demi kerahasiaan evaluasi.')"
                            class="w-full sm:w-auto shrink-0 px-8 py-3 bg-gradient-to-r from-gold-deep to-gold hover:from-gold hover:to-gold-deep text-white font-extrabold rounded-xl text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <span>Kirim Seluruh Penilaian</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="bg-white border border-slate-200 rounded-3xl p-8 sm:p-12 text-center shadow-card max-w-2xl mx-auto">
            <div class="w-20 h-20 rounded-3xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-4xl mx-auto mb-4 shadow-inner">
                🎉
            </div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Semua Penilaian Telah Disubmit!</h2>
            <p class="text-sm text-slate-600 mt-2 max-w-md mx-auto leading-relaxed">
                Terima kasih atas partisipasi Anda dalam proses evaluasi KPI Metode 360 Derajat berdasarkan SK Direksi No 016/SK-Dir/BSB.02/XII/2024. Kontribusi Anda sangat penting bagi kemajuan tim dan perusahaan.
            </p>
            <div class="mt-6">
                <a href="{{ route('questionnaire.start') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-navy text-white text-xs font-bold hover:bg-navy-deep transition shadow-sm">
                    Kembali ke Halaman Utama
                </a>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    document.querySelectorAll('[data-score-row]').forEach(function (row) {
        var slider = row.querySelector('[data-score-slider]');
        var number = row.querySelector('[data-score-input]');
        var bubble = row.querySelector('[data-score-value]');
        var na = row.querySelector('[data-not-observed]');
        if (!slider || !number || !bubble) return;

        function paint(v) {
            bubble.textContent = (v === '' || v === null || v === undefined) ? '—' : parseFloat(v).toFixed(1);
            if (v !== '' && v !== null && v !== undefined) {
                bubble.classList.remove('bg-navy');
                bubble.classList.add('bg-gold-deep');
            } else {
                bubble.classList.remove('bg-gold-deep');
                bubble.classList.add('bg-navy');
            }
        }

        function setDisabled(off) {
            number.disabled = off;
            slider.disabled = off;
            row.classList.toggle('opacity-50', off);
            if (off) {
                paint('—');
                bubble.textContent = 'N/A';
            }
        }

        if (number.value !== '') {
            slider.value = number.value;
            paint(number.value);
        }
        if (na && na.checked) {
            setDisabled(true);
        }

        slider.addEventListener('input', function () {
            number.value = slider.value;
            if (na && na.checked) {
                na.checked = false;
                setDisabled(false);
                number.value = slider.value;
            }
            paint(slider.value);
        });

        number.addEventListener('input', function () {
            if (number.value !== '') slider.value = number.value;
            paint(number.value);
        });

        if (na) {
            na.addEventListener('change', function () {
                setDisabled(na.checked);
            });
        }
    });
})();
</script>
@endpush
