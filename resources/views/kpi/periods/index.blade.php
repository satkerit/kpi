@extends('layouts.app')

@php
    $statusBadges = [
        'draft' => 'bg-slate-100 text-slate-700 ring-slate-500/20',
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'closed' => 'bg-gray-100 text-gray-600 ring-gray-500/20',
    ];
@endphp

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Master Data</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Periode Penilaian KPI</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola siklus penilaian tahunan dan jadwal aktif evaluasi.</p>
        </div>
        <a href="{{ route('periods.create') }}" class="btn-action-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Periode
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama Periode</th>
                        <th class="px-4 py-3">Tahun</th>
                        <th class="px-4 py-3">Rentang Jadwal</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($periods as $p)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">{{ $p->name }}</td>
                            <td class="px-4 py-3.5 num text-ink-soft">{{ $p->year }}</td>
                            <td class="px-4 py-3.5 num text-ink-muted text-xs">
                                {{ $p->start_date?->format('d M Y') }} s/d {{ $p->end_date?->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $statusBadges[$p->status] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5">
                                <a href="{{ route('admin.periods.config', $p) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Konfigurasi
                                </a>
                                <a href="{{ route('periods.edit', $p) }}" class="btn-table-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('periods.destroy', $p) }}" class="inline" onsubmit="return confirm('Hapus periode ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-table-delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-ink-muted">Belum ada data periode penilaian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $periods->links() }}</div>
    </div>
</main>
@endsection
