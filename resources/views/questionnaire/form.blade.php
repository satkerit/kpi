@extends('layouts.questionnaire')

@section('title', 'Isi Kuesioner KPI 360°')
@section('period_label', $period->name . ' · Tahun ' . $period->year)
@section('evaluator', $me->name)

@php
    $total = count($sections);
    $done = collect($sections)->where(fn ($s) => $s['assignment']->status === 'submitted')->count();
    $progress = $total > 0 ? round($done / $total * 100) : 100;
    $typeColors = [
        'P3' => 'bg-violet-100 text-violet-700 ring-violet-600/20',
        'P2' => 'bg-sky-100 text-sky-700 ring-sky-600/20',
        'P1' => 'bg-emerald-100 text-emerald-700 ring-emerald-600/20',
        'SELF' => 'bg-slate-200 text-slate-700 ring-slate-500/20',
    ];
@endphp
@section('progress', (string) $progress)

@section('content')
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-2xl px-5 py-4 text-sm font-semibold mb-6 shadow-card">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-rose-50 border border-rose-200 text-rose-700 rounded-2xl px-5 py-4 text-sm font-semibold mb-6 shadow-card">{{ session('error') }}</div>
    @endif

    {{-- Ringkasan + panduan --}}
    <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6 mb-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg sm:text-xl font-extrabold tracking-tight">Formulir Penilaian {{ $period->name }}</h1>
                <p class="text-sm text-ink-muted mt-1">{{ $done }} dari {{ $total }} penilaian sudah disubmit.</p>
            </div>
            <span class="inline-flex items-center rounded-full bg-navy-tint text-navy px-4 py-1.5 text-sm font-bold num">Nilai Min: 0 · Maks: {{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}</span>
        </div>
        <div class="mt-4 rounded-xl bg-gold-soft/60 border border-gold/20 px-4 py-3 text-[13px] leading-relaxed text-ink-soft">
            Isi <strong class="text-ink">semua penilaian di bawah dalam satu halaman ini</strong>, lalu tekan tombol
            <strong class="text-ink">Kirim Semua Penilaian</strong> di bagian bawah. Geser slider atau ketik angka 0–{{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}.
            Jika aspek belum pernah Anda amati, centang <em>“Tidak Diamati”</em>.
        </div>
        {{-- Navigasi cepat antar pegawai yang dinilai --}}
        @if($total > 1)
            <nav class="mt-4 flex flex-wrap gap-2">
                @foreach($sections as $i => $s)
                    <a href="#section-{{ $s['assignment']->id }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-ink-soft hover:border-navy hover:text-navy transition">
                        <span class="num text-ink-muted">{{ $i + 1 }}.</span> {{ $s['assignment']->evaluatee->name }}
                        @if($s['assignment']->status === 'submitted')
                            <span class="text-emerald-500">✓</span>
                        @endif
                    </a>
                @endforeach
            </nav>
        @endif
    </div>

    @if($pendingCount > 0)
        <form method="POST" action="{{ route('questionnaire.submitAll') }}">
            @csrf

            @foreach($sections as $i => $s)
                @php
                    $a = $s['assignment'];
                    $submitted = $a->status === 'submitted';
                @endphp
                <section id="section-{{ $a->id }}" class="bg-white rounded-2xl shadow-card mb-5 overflow-hidden scroll-mt-24">
                    {{-- Kepala section: pegawai yang dinilai --}}
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100 flex flex-wrap items-center gap-3 {{ $submitted ? 'bg-emerald-50/50' : 'bg-navy-tint/60' }}">
                        <span class="num w-8 h-8 rounded-full {{ $submitted ? 'bg-emerald-100 text-emerald-700' : 'bg-navy text-white' }} flex items-center justify-center text-sm font-extrabold shrink-0">{{ $i + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="font-extrabold text-ink truncate">{{ $a->evaluatee->name }}</p>
                            <p class="text-xs text-ink-muted truncate">NIK {{ $a->evaluatee->nik }} · {{ $a->evaluatee->position?->name ?? '—' }}</p>
                        </div>
                        <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold ring-1 {{ $typeColors[$a->evaluator_type] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">{{ $labels[$a->evaluator_type] ?? $a->evaluator_type }}</span>
                        @if($submitted)
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold bg-emerald-100 text-emerald-700 ring-1 ring-emerald-600/20">✓ Sudah disubmit</span>
                        @else
                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-bold bg-amber-100 text-amber-700 ring-1 ring-amber-600/20">Belum diisi</span>
                        @endif
                    </div>

                    @if($submitted)
                        {{-- Ringkasan read-only untuk yang sudah disubmit --}}
                        <dl class="px-5 sm:px-6 py-4 grid sm:grid-cols-2 gap-x-6 gap-y-2">
                            @foreach($a->scores as $score)
                                <div class="flex items-center justify-between gap-3 text-sm py-1 border-b border-dashed border-gray-100">
                                    <dt class="text-ink-soft truncate">{{ $score->subcriteria?->name ?? '—' }}</dt>
                                    <dd class="num font-bold text-ink shrink-0">{{ $score->raw_score === null ? 'Tidak diamati' : number_format((float) $score->raw_score, 1) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    @else
                        {{-- Baris subkriteria --}}
                        <div class="divide-y divide-gray-100">
                            @foreach($s['subcriteria'] as $j => $sc)
                                @php
                                    $field = "scores.{$a->id}.{$sc->id}";
                                    $oldVal = old("scores.{$a->id}.{$sc->id}");
                                    $naChecked = old("not_observed.{$a->id}.{$sc->id}");
                                @endphp
                                <div class="px-5 sm:px-6 py-4" data-score-row>
                                    <div class="flex items-start justify-between gap-3 mb-2">
                                        <div class="min-w-0">
                                            <p class="text-sm font-bold text-ink">{{ $j + 1 }}. {{ $sc->name }} <span class="ml-1 text-[11px] font-semibold text-ink-muted num">bobot {{ rtrim(rtrim(number_format((float) $sc->weight, 2), '0'), '.') }}%</span></p>
                                            @if($sc->description)
                                                <p class="text-xs text-ink-muted mt-0.5">{{ $sc->description }}</p>
                                            @endif
                                        </div>
                                        <span class="num text-xl font-extrabold text-navy shrink-0 min-w-[3rem] text-right" data-score-value>—</span>
                                    </div>
                                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                        <input type="range" min="0" max="{{ $maxScore }}" step="0.5" value="0" data-score-slider
                                               class="flex-1 w-full accent-[#16324f] h-2 cursor-pointer" aria-label="Nilai {{ $sc->name }}">
                                        <div class="flex items-center gap-2 shrink-0">
                                            <input type="number" name="scores[{{ $a->id }}][{{ $sc->id }}]" value="{{ $oldVal }}" min="0" max="{{ $maxScore }}" step="0.5"
                                                   placeholder="0–{{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}" data-score-input
                                                   class="num w-24 border border-gray-200 rounded-lg px-3 py-2 text-sm font-bold text-center focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none disabled:bg-gray-50 disabled:text-gray-400">
                                            <span class="text-xs text-ink-muted num">/ {{ rtrim(rtrim(number_format($maxScore, 2), '0'), '.') }}</span>
                                        </div>
                                    </div>
                                    @if($allowNotObserved)
                                        <label class="mt-2 inline-flex items-center gap-2 text-xs font-medium text-ink-muted cursor-pointer select-none">
                                            <input type="checkbox" name="not_observed[{{ $a->id }}][{{ $sc->id }}]" value="1" @checked($naChecked) data-not-observed
                                                   class="w-4 h-4 rounded accent-[#8a6a12]">
                                            Tidak Diamati <span class="text-ink-muted/70">(belum pernah melihat aspek ini)</span>
                                        </label>
                                    @endif
                                    <details class="mt-2">
                                        <summary class="text-xs font-semibold text-navy cursor-pointer hover:underline w-fit">+ Tambah catatan (opsional)</summary>
                                        <textarea name="comments[{{ $a->id }}][{{ $sc->id }}]" rows="2" maxlength="1000" placeholder="Catatan untuk {{ strtolower($sc->name) }}..."
                                                  class="mt-2 w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none resize-y">{{ old("comments.{$a->id}.{$sc->id}") }}</textarea>
                                    </details>
                                    @error($field)
                                        <p class="mt-1.5 text-xs font-semibold text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endforeach

            {{-- Spacer + tombol kirim fixed --}}
            <div class="h-24"></div>
            <div class="fixed bottom-0 inset-x-0 z-30 bg-white/95 backdrop-blur border-t border-gray-200 shadow-[0_-4px_20px_rgba(22,35,58,.10)]">
                <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
                    <p class="text-xs sm:text-sm text-ink-muted"><strong class="text-ink num">{{ $pendingCount }}</strong> penilaian belum diisi. Pastikan semua terisi sebelum mengirim.</p>
                    <button type="submit" onclick="return confirm('Kirim seluruh penilaian sekarang? Data yang sudah dikirim tidak dapat diubah.')"
                            class="shrink-0 px-6 sm:px-8 py-3 bg-gold text-white rounded-xl text-sm font-extrabold hover:bg-gold-deep transition shadow-card">
                        Kirim Semua Penilaian
                    </button>
                </div>
            </div>
        </form>
    @else
        <div class="bg-emerald-50 border border-emerald-200 rounded-2xl px-6 py-10 text-center shadow-card">
            <p class="text-3xl mb-2">🎉</p>
            <p class="font-extrabold text-emerald-800">Semua penilaian sudah disubmit.</p>
            <p class="text-sm text-emerald-700 mt-1">Terima kasih atas partisipasi Anda dalam penilaian KPI 360°.</p>
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

        function paint(v) { bubble.textContent = (v === '' || v === null || v === undefined) ? '—' : v; }
        function setDisabled(off) {
            number.disabled = off;
            slider.disabled = off;
            row.classList.toggle('opacity-60', off);
            if (off) paint('—');
        }

        if (number.value !== '') { slider.value = number.value; paint(number.value); }
        if (na && na.checked) setDisabled(true);

        slider.addEventListener('input', function () {
            number.value = slider.value;
            if (na && na.checked) { na.checked = false; setDisabled(false); number.value = slider.value; }
            paint(slider.value);
        });
        number.addEventListener('input', function () {
            if (number.value !== '') slider.value = number.value;
            paint(number.value);
        });
        if (na) na.addEventListener('change', function () { setDisabled(na.checked); });
    });
})();
</script>
@endpush
