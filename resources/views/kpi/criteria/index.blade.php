@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Master Data Kinerja</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Kriteria & Bobot Penilaian</h1>
            <p class="text-sm text-ink-muted mt-1">Konfigurasi aspek kompetensi, indikator subkriteria, serta bobot per tipe evaluator (P1, P2, P3).</p>
        </div>
        <div class="bg-white rounded-xl px-4 py-2.5 shadow-card border border-slate-100 flex items-center gap-3 text-xs">
            <span class="inline-block w-2.5 h-2.5 rounded-full bg-navy"></span>
            <span class="font-medium text-ink-soft">SK Direksi No. 016/SK-Dir/BSB.02/XII/2024</span>
        </div>
    </div>

    {{-- Form Tambah Subkriteria --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6 mb-8">
        <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Subkriteria Baru
        </h2>
        <form method="POST" action="{{ route('criteria.subcriteria.store') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            @csrf
            <div class="md:col-span-3">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Kriteria Induk</label>
                <select name="criteria_id" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    @foreach($criteria as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-4">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Subkriteria</label>
                <input type="text" name="name" placeholder="Misal: Inisiatif & Kreativitas" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Bobot (%)</label>
                <input type="number" name="weight" min="0" max="100" step="0.01" placeholder="10" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Tipe Penilai</label>
                <select name="evaluator_type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    <option value="P1">P1 (Atasan Langsung)</option>
                    <option value="P2">P2 (Rekan Sejawat)</option>
                    <option value="P3">P3 (Bawahan Langsung)</option>
                </select>
            </div>
            <div class="md:col-span-1">
                <button type="submit" class="w-full py-2 bg-navy text-white text-sm font-bold rounded-xl shadow hover:bg-navy-deep transition">
                    Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar Kriteria & Subkriteria --}}
    <div class="space-y-6">
        @foreach($criteria as $c)
            <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/75 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-extrabold text-ink text-base">{{ $c->name }}</h3>
                        <p class="text-xs text-ink-muted mt-0.5">{{ $c->description ?? 'Aspek kompetensi penilaian kinerja' }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-navy-tint text-navy">
                        {{ $c->subcriteria_count }} Subkriteria
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-ink-muted bg-white">
                                <th class="px-6 py-3">Nama Subkriteria</th>
                                <th class="px-4 py-3 text-center">Tipe Penilai</th>
                                <th class="px-4 py-3 text-center">Bobot Relatif</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($c->subcriteria as $sc)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3.5 font-medium text-ink">{{ $sc->name }}</td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($sc->evaluator_type === 'P1')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">P1 (Atasan)</span>
                                        @elseif($sc->evaluator_type === 'P2')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-600/20">P2 (Rekan)</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">P3 (Bawahan)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-ink num">{{ number_format($sc->weight, 2) }}%</td>
                                    <td class="px-6 py-3.5 text-right">
                                        <form method="POST" action="{{ route('criteria.subcriteria.destroy', $sc) }}" onsubmit="return confirm('Hapus subkriteria ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-ink-muted">
                                        Belum ada subkriteria terdaftar pada kriteria ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
</main>
@endsection
