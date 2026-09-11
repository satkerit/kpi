@extends('layouts.app')

@php
    $predicateColors = [
        'Sangat Baik' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 border-emerald-200',
        'Baik' => 'bg-sky-50 text-sky-700 ring-sky-600/20 border-sky-200',
        'Cukup Baik' => 'bg-amber-50 text-amber-700 ring-amber-600/20 border-amber-200',
        'Buruk' => 'bg-orange-50 text-orange-700 ring-orange-600/20 border-orange-200',
        'Sangat Buruk' => 'bg-rose-50 text-rose-700 ring-rose-600/20 border-rose-200',
    ];
@endphp

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">SK DIREKSI NO. 016/SK-DIR/BSB.02/XII/2024</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Laporan Penilaian KPI 360°</h1>
            <p class="text-sm text-ink-muted mt-1">Daftar rekapitulasi penilaian kinerja pegawai beserta dokumen lampiran penilaian detail.</p>
        </div>
        @if(session('success'))
            <div class="w-full bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm font-semibold shadow-sm">{{ session('success') }}</div>
        @endif
        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('kpi.calculateAll') }}" onsubmit="return confirm('Hitung nilai seluruh pegawai dengan penilaian submitted pada periode ini?')">
                @csrf
                <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
                <button type="submit" class="px-5 py-2.5 bg-navy text-white rounded-xl text-sm font-bold hover:bg-navy-deep transition shadow-card disabled:opacity-50" {{ $pendingCount > 0 ? 'disabled title=Masih_ada_penilaian_pending' : '' }}>
                    Hitung Keseluruhan
                </button>
            </form>
        </div>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('kpi.report') }}" class="bg-white rounded-2xl shadow-card p-4 mb-6 flex flex-wrap gap-3 items-end border border-slate-100">
        <div class="min-w-[200px]">
            <label class="block text-xs font-semibold text-ink-muted mb-1.5 uppercase tracking-wide">Periode Penilaian</label>
            <select name="period_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none">
                @foreach($periods as $p)
                    <option value="{{ $p->id }}" @selected($selectedPeriodId == $p->id)>{{ $p->name }} ({{ $p->year }})</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-semibold text-ink-muted mb-1.5 uppercase tracking-wide">Kantor</label>
            <select name="office_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none">
                <option value="">Semua Kantor</option>
                @foreach($offices as $o)
                    <option value="{{ $o->id }}" @selected(request('office_id') == $o->id)>{{ $o->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[160px]">
            <label class="block text-xs font-semibold text-ink-muted mb-1.5 uppercase tracking-wide">Divisi</label>
            <select name="division_id" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-navy/20 focus:border-navy outline-none">
                <option value="">Semua Divisi</option>
                @foreach($divisions as $d)
                    <option value="{{ $d->id }}" @selected(request('division_id') == $d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn-action-primary py-2 px-5">Terapkan Filter</button>
    </form>

    {{-- Tabel laporan --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden mb-8">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-ink text-base">Rekapitulasi Nilai Final Pegawai</h2>
            <span class="text-xs font-semibold text-ink-muted bg-slate-100 px-3 py-1 rounded-full">{{ $employees->total() }} pegawai</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3.5">Pegawai</th>
                        <th class="px-4 py-3.5">Kantor / Divisi</th>
                        <th class="px-3 py-3.5 text-center" title="Penilaian masuk">Masuk</th>
                        <th class="px-3 py-3.5 text-center" title="Menunggu">Pending</th>
                        <th class="px-3 py-3.5 text-center" title="Penilaian Atasan Langsung">P1</th>
                        <th class="px-3 py-3.5 text-center" title="Penilaian Rekan Sejawat">P2</th>
                        <th class="px-3 py-3.5 text-center" title="Penilaian Bawahan Langsung">P3</th>
                        <th class="px-3 py-3.5 text-center">Nilai Akhir</th>
                        <th class="px-3 py-3.5 text-center">Predikat</th>
                        <th class="px-5 py-3.5 text-center">Laporan & Detail SK</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $e)
                        @php $r = $e->kpiResults->first(); @endphp
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-4 font-semibold text-ink">
                                {{ $e->name }}
                                <br><span class="text-xs font-mono text-ink-muted">NIK {{ $e->nik }}</span>
                            </td>
                            <td class="px-4 py-4 text-ink-soft">
                                {{ $e->office?->name ?? '—' }}
                                <br><span class="text-xs text-ink-muted">{{ $e->division?->name ?? '—' }}</span>
                            </td>
                            <td class="px-3 py-4 text-center num font-bold text-emerald-600">{{ $e->submitted_count }}</td>
                            <td class="px-3 py-4 text-center num font-bold text-amber-600">{{ $e->pending_count }}</td>
                            @if($r)
                                <td class="px-3 py-4 text-center num font-bold text-ink-soft">{{ number_format((float) $r->score_p1, 1) }}</td>
                                <td class="px-3 py-4 text-center num font-bold text-ink-soft">{{ number_format((float) $r->score_p2, 1) }}</td>
                                <td class="px-3 py-4 text-center num font-bold text-ink-soft">{{ number_format((float) $r->score_p3, 1) }}</td>
                                <td class="px-3 py-4 text-center num text-base font-extrabold text-navy">{{ number_format((float) $r->final_score, 1) }}</td>
                                <td class="px-3 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $predicateColors[$r->predicate] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">{{ $r->predicate }}</span>
                                </td>
                            @else
                                <td colspan="4" class="px-3 py-4 text-center text-xs text-ink-muted italic">Belum dihitung</td>
                                <td class="px-3 py-4 text-center text-ink-muted">—</td>
                            @endif
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('kpi.report', array_merge(request()->query(), ['detail_id' => $e->id])) }}"
                                       class="btn-action-primary py-1.5 px-3 text-xs">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        Format SK Excel
                                    </a>
                                    <form method="POST" action="{{ route('kpi.calculate') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="evaluatee_id" value="{{ $e->id }}">
                                        <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
                                        <button type="submit" class="btn-action-gold py-1.5 px-2.5 text-xs">Hitung</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-16 text-center">
                                <p class="font-semibold text-ink mb-1">Belum ada pegawai</p>
                                <p class="text-sm text-ink-muted">Tambahkan pegawai terlebih dahulu di Manajemen Pegawai.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $employees->links() }}</div>
    </div>

    {{-- MODAL / VIEW LAPORAN SK DIREKSI LENGKAP SAMA PERSIS EXCEL --}}
    @if(isset($detailData) && $detailData['employee'])
        @php
            $emp = $detailData['employee'];
            $hasScores = $detailData['has_scores'];
        @endphp
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto" id="skReportModal">
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[92vh] overflow-y-auto flex flex-col">
                {{-- Header Modal --}}
                <div class="px-6 py-4 bg-navy-deep text-white flex items-center justify-between sticky top-0 z-20 shadow-md">
                    <div>
                        <p class="text-[10px] font-bold text-gold uppercase tracking-widest">LAMPIRAN SK DIREKSI NOMOR 016/SK-DIR/BSB.02/XII/2024</p>
                        <h3 class="text-lg font-extrabold tracking-tight">Form & Laporan Penilaian KPI Metode 360 Derajat</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <button onclick="window.print()" class="px-3 py-1.5 bg-gold text-navy-deep rounded-lg text-xs font-bold hover:bg-gold-soft transition flex items-center gap-1.5 shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            Cetak Laporan
                        </button>
                        <a href="{{ route('kpi.report', request()->except('detail_id')) }}" class="w-8 h-8 rounded-full bg-white/10 text-white flex items-center justify-center hover:bg-white/20 transition font-bold">✕</a>
                    </div>
                </div>

                <div class="p-6 sm:p-8 space-y-6 text-sm text-ink print:p-0">
                    {{-- Header Dokumen SK --}}
                    <div class="border-b-2 border-slate-900 pb-4 text-center">
                        <p class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-1">Lampiran SK Direksi Nomor 016/SK-Dir/BSB.02/XII/2024</p>
                        <h2 class="text-xl font-extrabold text-navy tracking-wide uppercase">LAPORAN PENILAIAN KPI METODE 360 DERAJAT</h2>
                    </div>

                    {{-- Profil Karyawan Yang Dinilai --}}
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-2 text-sm font-medium">
                        <div class="flex"><span class="w-44 text-slate-500">Nama Karyawan Yang Dinilai</span><span class="font-bold text-ink">: {{ $emp->name }}</span></div>
                        <div class="flex"><span class="w-44 text-slate-500">Jabatan / Bagian</span><span class="font-bold text-ink">: {{ $emp->position?->name ?? 'Staff' }} / {{ $emp->division?->name ?? '—' }}</span></div>
                        <div class="flex"><span class="w-44 text-slate-500">NIK</span><span class="font-bold font-mono text-ink">: {{ $emp->nik }}</span></div>
                        <div class="flex"><span class="w-44 text-slate-500">Periode / Tanggal dibuat</span><span class="font-bold text-ink">: {{ $selectedPeriod?->name ?? 'Periode Active' }} / {{ date('d-m-Y') }}</span></div>
                    </div>

                    @if(! $hasScores)
                        <div class="p-8 text-center bg-amber-50 rounded-xl border border-amber-200 text-amber-800 font-semibold">
                            Belum ada lembar kuesioner yang disubmit untuk pegawai ini pada periode yang dipilih.
                        </div>
                    @else
                        {{-- Tabel Penilaian Detail Berdasarkan Sheet SK --}}
                        <div class="border border-slate-300 rounded-xl overflow-hidden shadow-sm">
                            <table class="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr class="bg-navy text-white font-bold uppercase tracking-wider text-[11px]">
                                        <th class="p-3 border-b border-navy-deep w-28">Penilai</th>
                                        <th class="p-3 border-b border-navy-deep w-40">Kriteria</th>
                                        <th class="p-3 border-b border-navy-deep">Subkriteria</th>
                                        <th class="p-3 border-b border-navy-deep text-center w-20">Nilai Input</th>
                                        <th class="p-3 border-b border-navy-deep text-center w-24">Bobot (%)</th>
                                        <th class="p-3 border-b border-navy-deep text-center w-24">Nilai Tertimbang</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200 font-medium">
                                    {{-- Group P1 Atasan --}}
                                    @if(isset($detailData['scores_by_type']['P1']['items']) && count($detailData['scores_by_type']['P1']['items']) > 0)
                                        @foreach($detailData['scores_by_type']['P1']['items'] as $idx => $item)
                                            <tr class="hover:bg-slate-50">
                                                @if($idx === 0)
                                                    <td rowspan="{{ count($detailData['scores_by_type']['P1']['items']) }}" class="p-3 font-extrabold text-navy bg-slate-50 border-r border-slate-200 align-top">Atasan (P1)</td>
                                                @endif
                                                <td class="p-2.5 font-bold text-slate-700">{{ $item['criteria_name'] }}</td>
                                                <td class="p-2.5 text-slate-800">{{ $item['subcriteria_name'] }}</td>
                                                <td class="p-2.5 text-center font-bold font-mono">{{ number_format($item['avg_raw'], 1) }}</td>
                                                <td class="p-2.5 text-center font-mono">{{ number_format($item['weight'], 1) }}%</td>
                                                <td class="p-2.5 text-center font-bold font-mono text-navy">{{ number_format($item['weighted_score'], 1) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-blue-50/70 font-bold border-t-2 border-slate-300">
                                            <td colspan="5" class="p-2.5 text-right uppercase text-blue-900 tracking-wider">Total Nilai P1 (Atasan)</td>
                                            <td class="p-2.5 text-center font-mono text-base text-blue-900">{{ number_format($detailData['p1_total'], 1) }}</td>
                                        </tr>
                                    @endif

                                    {{-- Group P2 Rekan --}}
                                    @if(isset($detailData['scores_by_type']['P2']['items']) && count($detailData['scores_by_type']['P2']['items']) > 0)
                                        @foreach($detailData['scores_by_type']['P2']['items'] as $idx => $item)
                                            <tr class="hover:bg-slate-50">
                                                @if($idx === 0)
                                                    <td rowspan="{{ count($detailData['scores_by_type']['P2']['items']) }}" class="p-3 font-extrabold text-purple-900 bg-slate-50 border-r border-slate-200 align-top">Rekan (P2)</td>
                                                @endif
                                                <td class="p-2.5 font-bold text-slate-700">{{ $item['criteria_name'] }}</td>
                                                <td class="p-2.5 text-slate-800">{{ $item['subcriteria_name'] }}</td>
                                                <td class="p-2.5 text-center font-bold font-mono">{{ number_format($item['avg_raw'], 1) }}</td>
                                                <td class="p-2.5 text-center font-mono">{{ number_format($item['weight'], 1) }}%</td>
                                                <td class="p-2.5 text-center font-bold font-mono text-purple-900">{{ number_format($item['weighted_score'], 1) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-purple-50/70 font-bold border-t-2 border-slate-300">
                                            <td colspan="5" class="p-2.5 text-right uppercase text-purple-900 tracking-wider">Total Nilai P2 (Rekan Sejawat)</td>
                                            <td class="p-2.5 text-center font-mono text-base text-purple-900">{{ number_format($detailData['p2_total'], 1) }}</td>
                                        </tr>
                                    @endif

                                    {{-- Group P3 Bawahan --}}
                                    @if(isset($detailData['scores_by_type']['P3']['items']) && count($detailData['scores_by_type']['P3']['items']) > 0)
                                        @foreach($detailData['scores_by_type']['P3']['items'] as $idx => $item)
                                            <tr class="hover:bg-slate-50">
                                                @if($idx === 0)
                                                    <td rowspan="{{ count($detailData['scores_by_type']['P3']['items']) }}" class="p-3 font-extrabold text-amber-900 bg-slate-50 border-r border-slate-200 align-top">Bawahan (P3)</td>
                                                @endif
                                                <td class="p-2.5 font-bold text-slate-700">{{ $item['criteria_name'] }}</td>
                                                <td class="p-2.5 text-slate-800">{{ $item['subcriteria_name'] }}</td>
                                                <td class="p-2.5 text-center font-bold font-mono">{{ number_format($item['avg_raw'], 1) }}</td>
                                                <td class="p-2.5 text-center font-mono">{{ number_format($item['weight'], 1) }}%</td>
                                                <td class="p-2.5 text-center font-bold font-mono text-amber-900">{{ number_format($item['weighted_score'], 1) }}</td>
                                            </tr>
                                        @endforeach
                                        <tr class="bg-amber-50/70 font-bold border-t-2 border-slate-300">
                                            <td colspan="5" class="p-2.5 text-right uppercase text-amber-900 tracking-wider">Total Nilai P3 (Bawahan)</td>
                                            <td class="p-2.5 text-center font-mono text-base text-amber-900">{{ number_format($detailData['p3_total'], 1) }}</td>
                                        </tr>
                                    @endif

                                    {{-- Ringkasan Total P1 + P2 + P3 & Rating --}}
                                    <tr class="bg-slate-100 font-extrabold border-t-2 border-slate-400 text-sm">
                                        <td colspan="5" class="p-3 text-right uppercase text-slate-800 tracking-wider">Total Nilai P1 + P2 + P3</td>
                                        <td class="p-3 text-center font-mono text-lg text-slate-900">{{ number_format($detailData['final_sum'], 1) }}</td>
                                    </tr>
                                    <tr class="bg-navy text-white font-extrabold text-base">
                                        <td colspan="3" class="p-3.5 uppercase tracking-wider text-gold">Final Performance Appraisal Rating</td>
                                        <td colspan="2" class="p-3.5 text-center uppercase tracking-widest text-emerald-300">{{ $detailData['predicate'] }}</td>
                                        <td class="p-3.5 text-center font-mono text-xl text-white">{{ number_format($detailData['final_score'], 1) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Daftar Nama Penilai Sesuai Template Excel SK --}}
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <h4 class="font-bold text-slate-800 uppercase tracking-wider text-xs mb-3">Daftar Tim Penilai (Sesuai SK Direksi):</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                                <div class="bg-white p-3 rounded-lg border border-slate-200">
                                    <p class="font-bold text-blue-900 border-b pb-1 mb-2">1. Atasan (P1)</p>
                                    @forelse($detailData['raters']['P1'] as $r)
                                        <p class="font-semibold text-slate-800">• {{ $r->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $r->position?->name ?? 'Atasan' }} - {{ $r->office?->name ?? '' }}</p>
                                    @empty
                                        <p class="text-slate-400 italic">Tidak ada penilai P1</p>
                                    @endforelse
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-slate-200">
                                    <p class="font-bold text-purple-900 border-b pb-1 mb-2">2. Rekan (P2)</p>
                                    @forelse($detailData['raters']['P2'] as $r)
                                        <p class="font-semibold text-slate-800">• {{ $r->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $r->position?->name ?? 'Staff' }} - {{ $r->office?->name ?? '' }}</p>
                                    @empty
                                        <p class="text-slate-400 italic">Tidak ada penilai P2</p>
                                    @endforelse
                                </div>
                                <div class="bg-white p-3 rounded-lg border border-slate-200">
                                    <p class="font-bold text-amber-900 border-b pb-1 mb-2">3. Bawahan (P3)</p>
                                    @forelse($detailData['raters']['P3'] as $r)
                                        <p class="font-semibold text-slate-800">• {{ $r->name }}</p>
                                        <p class="text-[11px] text-slate-500">{{ $r->position?->name ?? 'Staff' }} - {{ $r->office?->name ?? '' }}</p>
                                    @empty
                                        <p class="text-slate-400 italic">Tidak ada penilai P3</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        {{-- Tanda Tangan & Kategori Predikat --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4">
                            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50 text-xs">
                                <h4 class="font-bold text-slate-800 mb-2 uppercase tracking-wider">Standar Kategori Hasil Evaluasi Kinerja (SK 360°)</h4>
                                <table class="w-full border-collapse">
                                    <thead class="bg-slate-200 text-slate-700 font-bold">
                                        <tr><th class="p-1.5 text-left">Nilai Evaluasi</th><th class="p-1.5 text-left">Hasil Evaluasi (Kinerja)</th></tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-200 font-medium">
                                        <tr><td class="p-1.5 font-mono font-bold text-emerald-700">&gt; 9.5 - 10</td><td class="p-1.5 font-bold text-emerald-700">Sangat Baik</td></tr>
                                        <tr><td class="p-1.5 font-mono font-bold text-sky-700">&gt; 7.5 - 9.5</td><td class="p-1.5 font-bold text-sky-700">Baik</td></tr>
                                        <tr><td class="p-1.5 font-mono font-bold text-amber-700">&gt; 5.5 - 7.5</td><td class="p-1.5 font-bold text-amber-700">Cukup Baik</td></tr>
                                        <tr><td class="p-1.5 font-mono font-bold text-orange-700">&gt; 3.5 - 5.5</td><td class="p-1.5 font-bold text-orange-700">Buruk</td></tr>
                                        <tr><td class="p-1.5 font-mono font-bold text-rose-700">0 - 3.5</td><td class="p-1.5 font-bold text-rose-700">Sangat Buruk</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="flex flex-col justify-between text-xs text-center p-4">
                                <p class="font-semibold text-slate-600">Diterima & Disahkan oleh Divisi SDI & Personalia</p>
                                <div class="my-8">
                                    <p class="text-slate-400 italic">[ Tanda Tangan Digital / Stempel ]</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900 underline">......................................................</p>
                                    <p class="font-bold text-slate-700">Kadiv. SDI & Personalia</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</main>
@endsection
