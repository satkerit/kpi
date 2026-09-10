@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Bagian</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola bagian/departemen di bawah divisi dan kantor.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.import.template', 'departments') }}" class="inline-flex items-center gap-2 bg-white border border-slate-200 text-ink rounded-xl px-4 py-2.5 text-sm font-semibold hover:bg-slate-50 transition shadow-card">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Template CSV
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="inline-flex items-center gap-2 bg-emerald-600 text-white rounded-xl px-4 py-2.5 text-sm font-semibold hover:bg-emerald-700 transition shadow-card">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload CSV
            </button>
            <a href="{{ route('admin.departments.create') }}" class="inline-flex items-center gap-2 bg-navy text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-navy-deep transition shadow-card">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Bagian
            </a>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-lg font-extrabold text-ink mb-2">Upload Data Bagian (CSV)</h3>
            <p class="text-xs text-ink-muted mb-4">Pastikan format sesuai dengan template CSV yang disediakan.</p>
            <form action="{{ route('admin.import.store', 'departments') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <input type="file" name="file" accept=".csv,.txt" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-navy-tint file:text-navy hover:file:bg-navy/10" required>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-ink-muted hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-navy text-white text-xs font-bold hover:bg-navy-deep">Upload & Proses</button>
                </div>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama Bagian</th>
                        <th class="px-4 py-3">Induk</th>
                        <th class="px-4 py-3">Kategori</th>
                        <th class="px-4 py-3">Kode</th>
                        <th class="px-4 py-3">Divisi</th>
                        <th class="px-4 py-3">Level Kantor</th>
                        <th class="px-4 py-3">Kepala Bagian</th>
                        <th class="px-4 py-3 text-center">Pegawai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($records as $department)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5 font-bold text-ink">
                                @if($department->parent_id)
                                    <span class="text-ink-muted">↳</span> {{ $department->name }}
                                @else
                                    {{ $department->name }}
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-ink-soft">
                                @if($department->parent)
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 bg-blue-50 text-blue-700 ring-blue-600/20">{{ $department->parent->name }}</span>
                                @else
                                    <span class="text-xs text-ink-muted">Bagian Utama</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5">
                                @if($department->category === 'operasional')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 bg-navy-tint text-navy ring-navy/20">Operasional</span>
                                @elseif($department->category === 'bisnis')
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 bg-gold-tint text-gold-deep ring-gold/30">Bisnis</span>
                                @else
                                    <span class="text-xs text-ink-muted">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5"><span class="font-mono text-xs font-semibold text-navy bg-navy-tint px-2 py-1 rounded-md">{{ $department->code }}</span></td>
                            <td class="px-4 py-3.5 text-ink-soft">{{ $department->division?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-ink-soft">
                                @if($department->office)
                                    @php
                                        $typeLabel = match($department->office->type) {
                                            'head_office' => ['label' => 'Pusat', 'class' => 'bg-navy-tint text-navy ring-navy/20'],
                                            'branch'      => ['label' => 'Cabang', 'class' => 'bg-blue-50 text-blue-700 ring-blue-600/20'],
                                            'kpo'         => ['label' => 'KPO', 'class' => 'bg-amber-50 text-amber-700 ring-amber-600/20'],
                                            'kas'         => ['label' => 'Kas', 'class' => 'bg-rose-50 text-rose-700 ring-rose-600/20'],
                                            default       => ['label' => ucfirst($department->office->type), 'class' => 'bg-gray-100 text-gray-600 ring-gray-500/20'],
                                        };
                                    @endphp
                                    <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $typeLabel['class'] }}">{{ $typeLabel['label'] }}</span>
                                    <span class="text-xs text-ink-muted ml-1">{{ $department->office->name }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-ink-soft">{{ $department->head?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center num font-semibold text-ink-soft">{{ $department->employees_count }}</td>
                            <td class="px-4 py-3.5 text-center">
                                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold ring-1 {{ $department->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-600/20' : 'bg-gray-100 text-gray-600 ring-gray-500/20' }}">
                                    {{ $department->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('admin.departments.edit', $department) }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                                <form method="POST" action="{{ route('admin.departments.destroy', $department) }}" class="inline" onsubmit="return confirm('Hapus bagian ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center text-ink-muted">Belum ada data bagian.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $records->links() }}</div>
    </div>
</main>
@endsection
