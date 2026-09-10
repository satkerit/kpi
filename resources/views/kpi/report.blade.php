@extends('layouts.app')

@php
    $predicateColors = [
        'Sangat Baik' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'Baik' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
        'Cukup Baik' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'Buruk' => 'bg-orange-50 text-orange-700 ring-orange-600/20',
        'Sangat Buruk' => 'bg-rose-50 text-rose-700 ring-rose-600/20',
    ];
@endphp

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    {{-- Header --}}
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Rekapitulasi & Perhitungan</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Laporan KPI 360°</h1>
            <p class="text-sm text-ink-muted mt-1">Daftar seluruh pegawai beserta nilai final pada periode yang sama.</p>
        </div>
        @if(session('success'))
            <div class="w-full bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl px-4 py-3 text-sm font-semibold">{{ session('success') }}</div>
        @endif
        <form method="POST" action="{{ route('kpi.calculateAll') }}" onsubmit="return confirm('Hitung nilai seluruh pegawai dengan penilaian submitted pada periode ini?')">
            @csrf
            <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
            <button type="submit" class="px-5 py-2.5 bg-navy text-white rounded-xl text-sm font-bold hover:bg-navy-deep transition shadow-card disabled:opacity-50" {{ $pendingCount > 0 ? 'disabled title= Masih ada penilaian pending' : '' }}>
                Hitung Keseluruhan
            </button>
            @if($pendingCount > 0)
                <p class="text-xs text-amber-600 mt-1.5 font-medium">{{ number_format($pendingCount) }} penilaian masih pending — hitung per pegawai saja.</p>
            @endif
        </form>
    </div>

    {{-- Filter bar --}}
    <form method="GET" action="{{ route('kpi.report') }}" class="bg-white rounded-2xl shadow-card p-4 mb-6 flex flex-wrap gap-3 items-end">
        <div class="min-w-[180px]">
            <label class="block text-xs font-semibold text-ink-muted mb-1.5 uppercase tracking-wide">Periode</label>
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
        <button class="bg-navy text-white rounded-lg px-5 py-2 text-sm font-semibold hover:bg-navy-deep transition shadow-sm">Terapkan Filter</button>
    </form>

    {{-- Tabel laporan --}}
    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-ink">Daftar Pegawai & Nilai Final</h2>
            <span class="text-xs text-ink-muted">{{ $employees->total() }} pegawai</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Pegawai</th>
                        <th class="px-4 py-3">Kantor / Divisi</th>
                        <th class="px-3 py-3 text-center" title="Penilaian masuk">Masuk</th>
                        <th class="px-3 py-3 text-center" title="Menunggu">Pending</th>
                        <th class="px-3 py-3 text-center" title="Penilaian Atasan Langsung">P1</th>
                        <th class="px-3 py-3 text-center" title="Penilaian Rekan Sejawat">P2</th>
                        <th class="px-3 py-3 text-center" title="Penilaian Bawahan Langsung">P3</th>
                        <th class="px-3 py-3 text-center">Nilai Akhir</th>
                        <th class="px-3 py-3 text-center">Predikat</th>
                        <th class="px-5 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $e)
                        @php $r = $e->kpiResults->first(); @endphp
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-semibold text-ink">{{ $e->name }}<br><span class="text-xs font-normal text-ink-muted">NIK {{ $e->nik }}</span></td>
                            <td class="px-4 py-3.5 text-ink-soft">{{ $e->office?->name ?? '—' }}<br><span class="text-xs text-ink-muted">{{ $e->division?->name ?? '—' }}</span></td>
                            <td class="px-3 py-3.5 text-center num font-semibold text-emerald-600">{{ $e->submitted_count }}</td>
                            <td class="px-3 py-3.5 text-center num font-semibold text-amber-600">{{ $e->pending_count }}</td>
                            @if($r)
                                <td class="px-3 py-3.5 text-center num font-semibold text-ink-soft">{{ number_format((float) $r->score_p1, 2) }}</td>
                                <td class="px-3 py-3.5 text-center num font-semibold text-ink-soft">{{ number_format((float) $r->score_p2, 2) }}</td>
                                <td class="px-3 py-3.5 text-center num font-semibold text-ink-soft">{{ number_format((float) $r->score_p3, 2) }}</td>
                                <td class="px-3 py-3.5 text-center num text-base font-extrabold text-navy">{{ number_format((float) $r->final_score, 2) }}</td>
                                <td class="px-3 py-3.5 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-bold ring-1 {{ $predicateColors[$r->predicate] ?? 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">{{ $r->predicate }}</span>
                                </td>
                            @else
                                <td colspan="4" class="px-3 py-3.5 text-center text-xs text-ink-muted italic">Belum dihitung</td>
                                <td class="px-3 py-3.5 text-center text-ink-muted">—</td>
                            @endif
                            <td class="px-5 py-3.5 text-center">
                                <form method="POST" action="{{ route('kpi.calculate') }}">
                                    @csrf
                                    <input type="hidden" name="evaluatee_id" value="{{ $e->id }}">
                                    <input type="hidden" name="period_id" value="{{ $selectedPeriodId }}">
                                    <button type="submit" class="px-3 py-1.5 bg-gold-soft text-gold-deep rounded-lg text-xs font-bold hover:bg-gold hover:text-white transition whitespace-nowrap">Hitung</button>
                                </form>
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
</main>
@endsection
