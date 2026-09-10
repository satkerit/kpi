@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    {{-- Header --}}
    <div class="mb-8">
        <a href="{{ route('periods.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Periode
        </a>
        <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Konfigurasi Periode</p>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $period->name }}</h1>
        <p class="text-sm text-ink-muted mt-1">Tahun {{ $period->year }} &middot; Konfigurasi bobot evaluator, skala penilaian, dan pengaturan 360.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

        {{-- === BOBOT EVALUATOR === --}}
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Bobot Tipe Evaluator</h2>
                <p class="text-xs text-ink-muted mt-0.5">Persentase kontribusi setiap tipe penilai terhadap nilai akhir.</p>
            </div>
            <form method="POST" action="{{ route('admin.periods.config.weights', $period) }}" class="p-6 space-y-4">
                @csrf
                @php
                    $typeLabels = [
                        'P1'   => 'P1 — Atasan menilai bawahan',
                        'P2'   => 'P2 — Rekan sejawat',
                        'P3'   => 'P3 — Bawahan menilai atasan',
                        'SELF' => 'SELF — Penilaian diri sendiri',
                    ];
                    $typeColors = [
                        'P1'   => 'bg-blue-50 text-blue-700 ring-blue-600/20',
                        'P2'   => 'bg-purple-50 text-purple-700 ring-purple-600/20',
                        'P3'   => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                        'SELF' => 'bg-teal-50 text-teal-700 ring-teal-600/20',
                    ];
                @endphp
                @foreach($typeLabels as $type => $label)
                    <div class="flex items-center gap-4">
                        <span class="inline-flex rounded-md px-2.5 py-1 text-xs font-bold ring-1 w-40 justify-center {{ $typeColors[$type] }}">
                            {{ $type }}
                        </span>
                        <span class="flex-1 text-sm text-ink-soft">{{ $label }}</span>
                        <div class="flex items-center gap-1.5">
                            <input type="number" name="weights[{{ $type }}]"
                                value="{{ old("weights.$type", $weights[$type]?->weight ?? 1.00) }}"
                                min="0" max="100" step="0.01"
                                class="w-24 rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink text-right focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                            <span class="text-xs text-ink-muted">%</span>
                        </div>
                    </div>
                @endforeach

                @error('weights.*')
                    <p class="text-xs text-rose-600">{{ $message }}</p>
                @enderror

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                        Simpan Bobot
                    </button>
                </div>
            </form>
        </div>

        {{-- === PENGATURAN 360 === --}}
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Pengaturan Penilaian 360</h2>
                <p class="text-xs text-ink-muted mt-0.5">Parameter dinamis: anonimitas, batas penilai P2, dan opsi "Tidak Diamati".</p>
            </div>
            <form method="POST" action="{{ route('admin.periods.config.settings', $period) }}" class="p-6 space-y-5">
                @csrf
                @php
                    $defaults = \App\Domains\MasterKpi\Models\KpiPeriod::DEFAULT_SETTINGS;
                @endphp

                {{-- Ambang anonimitas --}}
                <div>
                    <label class="block text-sm font-medium text-ink-soft mb-1">
                        Ambang Anonimitas per Grup
                        <span class="text-xs text-ink-muted font-normal">(min. penilai sebelum rata-rata ditampilkan)</span>
                    </label>
                    <input type="number" name="min_raters_per_group" min="1" max="50"
                        value="{{ old('min_raters_per_group', $period->setting('min_raters_per_group')) }}"
                        class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    <p class="text-xs text-ink-muted mt-1">Default: {{ $defaults['min_raters_per_group'] }} penilai. Standar 360: min. 3.</p>
                    @error('min_raters_per_group')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                {{-- Batas peer P2 --}}
                <div>
                    <label class="block text-sm font-medium text-ink-soft mb-1">
                        Batas Penilai P2 per Pegawai
                        <span class="text-xs text-ink-muted font-normal">(0 = tidak dibatasi)</span>
                    </label>
                    <input type="number" name="max_peers_per_evaluatee" min="0" max="100"
                        value="{{ old('max_peers_per_evaluatee', $period->setting('max_peers_per_evaluatee')) }}"
                        class="w-32 rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    <p class="text-xs text-ink-muted mt-1">Default: {{ $defaults['max_peers_per_evaluatee'] }}. Standar 360: ~6 penilai.</p>
                    @error('max_peers_per_evaluatee')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
                </div>

                {{-- Allow Not Observed --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="allow_not_observed" id="allow_not_observed" value="1"
                        {{ $period->setting('allow_not_observed') ? 'checked' : '' }}
                        class="mt-1 rounded border-slate-300 text-navy focus:ring-navy">
                    <div>
                        <label for="allow_not_observed" class="text-sm font-medium text-ink-soft cursor-pointer">
                            Izinkan opsi "Tidak Diamati"
                        </label>
                        <p class="text-xs text-ink-muted mt-0.5">Penilai dapat menandai item yang belum pernah diamati. Item tersebut dikeluarkan dari pembobotan akhir.</p>
                    </div>
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>

        {{-- === SKALA PENILAIAN === --}}
        <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden xl:col-span-2">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Skala Penilaian (Rating Scales)</h2>
                <p class="text-xs text-ink-muted mt-0.5">Rentang nilai beserta predikat yang akan tampil pada laporan.</p>
            </div>

            {{-- Daftar skala eksisting --}}
            @if($scales->isNotEmpty())
                <div class="overflow-x-auto border-b border-slate-100">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                                <th class="px-4 py-2.5">Label</th>
                                <th class="px-3 py-2.5 text-center">Min</th>
                                <th class="px-3 py-2.5 text-center">Max</th>
                                <th class="px-3 py-2.5">Predikat</th>
                                <th class="px-3 py-2.5">Warna</th>
                                <th class="px-3 py-2.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($scales as $scale)
                                <tr class="hover:bg-navy-tint/40 transition">
                                    <td class="px-4 py-2.5 font-semibold text-ink">{{ $scale->label }}</td>
                                    <td class="px-3 py-2.5 text-center num text-ink-soft">{{ $scale->min_value }}</td>
                                    <td class="px-3 py-2.5 text-center num text-ink-soft">{{ $scale->max_value }}</td>
                                    <td class="px-3 py-2.5 text-ink-soft">{{ $scale->predicate }}</td>
                                    <td class="px-3 py-2.5">
                                        @if($scale->color)
                                            <span class="inline-block w-5 h-5 rounded-full border border-slate-200" style="background:{{ $scale->color }}"></span>
                                        @else
                                            <span class="text-xs text-ink-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2.5 text-right">
                                        <form method="POST" action="{{ route('admin.periods.config.scales.destroy', [$period, $scale]) }}" onsubmit="return confirm('Hapus skala ini?')">
                                            @csrf @method('DELETE')
                                            <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-6 py-4 text-sm text-ink-muted">Belum ada skala penilaian untuk periode ini.</p>
            @endif

            {{-- Form tambah/ganti skala --}}
            <form method="POST" action="{{ route('admin.periods.config.scales', $period) }}" class="p-6" id="scales-form">
                @csrf
                <p class="text-xs font-bold text-ink-soft uppercase tracking-wider mb-3">Tambah / Ganti Skala</p>
                <p class="text-xs text-ink-muted mb-4">Mengisi form ini akan <strong>menggantikan seluruh skala</strong> periode ini. Tambahkan semua baris sebelum menyimpan.</p>

                <div id="scales-rows" class="space-y-3">
                    @forelse($scales as $i => $scale)
                        <div class="scale-row grid grid-cols-12 gap-2 items-center">
                            <input type="text"   name="scales[{{ $i }}][label]"      value="{{ $scale->label }}"      placeholder="Label"     class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
                            <input type="number" name="scales[{{ $i }}][min_value]"  value="{{ $scale->min_value }}"  placeholder="Min"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
                            <input type="number" name="scales[{{ $i }}][max_value]"  value="{{ $scale->max_value }}"  placeholder="Max"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
                            <input type="text"   name="scales[{{ $i }}][predicate]"  value="{{ $scale->predicate }}"  placeholder="Predikat"  class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
                            <input type="color"  name="scales[{{ $i }}][color]"      value="{{ $scale->color ?? '#64748b' }}"                  class="col-span-1 rounded-xl border border-slate-200 h-9 px-1 cursor-pointer">
                            <button type="button" onclick="this.closest('.scale-row').remove()" class="col-span-1 text-rose-500 hover:text-rose-700 text-lg font-bold">&times;</button>
                        </div>
                    @empty
                        <div class="scale-row grid grid-cols-12 gap-2 items-center">
                            <input type="text"   name="scales[0][label]"     placeholder="Label"     class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
                            <input type="number" name="scales[0][min_value]" placeholder="Min"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
                            <input type="number" name="scales[0][max_value]" placeholder="Max"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
                            <input type="text"   name="scales[0][predicate]" placeholder="Predikat"  class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
                            <input type="color"  name="scales[0][color]"     value="#64748b"          class="col-span-1 rounded-xl border border-slate-200 h-9 px-1 cursor-pointer">
                            <span class="col-span-1"></span>
                        </div>
                    @endforelse
                </div>

                <div class="mt-3 flex items-center gap-3">
                    <button type="button" id="add-scale-row" class="text-xs font-semibold text-navy hover:underline">
                        + Tambah Baris
                    </button>
                </div>

                @error('scales.*')
                    <p class="text-xs text-rose-600 mt-2">{{ $message }}</p>
                @enderror

                <div class="pt-4 flex justify-end">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gold-deep text-white text-sm font-bold shadow hover:opacity-90 transition">
                        Simpan Skala
                    </button>
                </div>
            </form>
        </div>

    </div>
</main>

<script>
document.getElementById('add-scale-row').addEventListener('click', function () {
    const container = document.getElementById('scales-rows');
    const idx = container.querySelectorAll('.scale-row').length;
    const row = document.createElement('div');
    row.className = 'scale-row grid grid-cols-12 gap-2 items-center';
    row.innerHTML = `
        <input type="text"   name="scales[${idx}][label]"     placeholder="Label"     class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
        <input type="number" name="scales[${idx}][min_value]" placeholder="Min"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
        <input type="number" name="scales[${idx}][max_value]" placeholder="Max"       class="col-span-2 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" step="0.01" required>
        <input type="text"   name="scales[${idx}][predicate]" placeholder="Predikat"  class="col-span-3 rounded-xl border border-slate-200 px-3 py-2 text-xs focus:border-navy outline-none" required>
        <input type="color"  name="scales[${idx}][color]"     value="#64748b"          class="col-span-1 rounded-xl border border-slate-200 h-9 px-1 cursor-pointer">
        <button type="button" onclick="this.closest('.scale-row').remove()" class="col-span-1 text-rose-500 hover:text-rose-700 text-lg font-bold">&times;</button>
    `;
    container.appendChild(row);
});

// Toggle input skor saat "Tidak Diamati" dicentang
document.addEventListener('change', function (e) {
    if (!e.target.matches('[data-not-observed]')) { return; }
    const id = e.target.dataset.notObserved;
    const input = document.querySelector(`[data-score-input="${id}"]`);
    if (!input) { return; }
    input.disabled = e.target.checked;
    input.required = !e.target.checked;
    if (e.target.checked) { input.value = ''; }
});
</script>
@endsection
