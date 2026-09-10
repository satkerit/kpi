@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Setup Penilaian</h1>
            <p class="text-sm text-ink-muted mt-1">Atur siapa menilai siapa berdasarkan jabatan, kantor, dan divisi. Kosongkan scope = berlaku untuk semua.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <button onclick="document.getElementById('generateModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 text-white rounded-xl px-4 py-2.5 text-sm font-semibold hover:bg-emerald-700 transition shadow-card">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Generate ke Periode
            </button>
            <a href="{{ route('admin.evaluation-rules.create') }}" class="inline-flex items-center gap-2 bg-navy text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-navy-deep transition shadow-card">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Rule
            </a>
        </div>
    </div>

    {{-- Generate Modal --}}
    <div id="generateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-lg font-extrabold text-ink mb-2">Generate Penugasan dari Rule</h3>
            <p class="text-xs text-ink-muted mb-4">Seluruh rule aktif akan diekspansi menjadi penugasan penilaian (KpiAssignment) pada periode yang dipilih. Duplikat otomatis dilewati.</p>
            <form action="{{ route('admin.evaluation-rules.generate') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Periode Penilaian</label>
                    <select name="period_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        <option value="">— Pilih Periode —</option>
                        @foreach($periods as $period)
                            <option value="{{ $period->id }}">{{ $period->name }} ({{ $period->year }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-ink-muted hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-bold hover:bg-emerald-700">Generate</button>
                </div>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-700 font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Rule</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Penilai</th>
                        <th class="px-4 py-3">Dinilai</th>
                        <th class="px-4 py-3">Scope</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $rule)
                        @php
                            $scope = collect([
                                $rule->scopeOffice?->code,
                                $rule->scopeDivision?->code,
                            ])->filter()->implode(' / ');
                            $typeClass = $rule->evaluator_type === 'P1'
                                ? 'bg-blue-50 text-blue-700 ring-blue-600/20'
                                : ($rule->evaluator_type === 'P2'
                                    ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'
                                    : 'bg-amber-50 text-amber-700 ring-amber-600/20');
                        @endphp
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">{{ $rule->name ?? '—' }}</td>
                            <td class="px-4 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $typeClass }}">
                                    {{ $rule->evaluator_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-ink-soft">{{ $rule->evaluatorPosition?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-ink-soft">{{ $rule->evaluateePosition?->name ?? 'Semua (level)' }}</td>
                            <td class="px-4 py-3.5 text-xs text-ink-muted">{{ $scope !== '' ? $scope : 'Semua unit' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $rule->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                                    {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.evaluation-rules.edit', $rule) }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.evaluation-rules.destroy', $rule) }}" class="inline" onsubmit="return confirm('Hapus rule ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-ink-muted">Belum ada rule penilaian. Tambahkan rule pertama.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $records->links() }}</div>
    </div>
</main>
@endsection
