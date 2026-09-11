@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Manajemen Admin</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Pegawai</h1>
            <p class="text-sm text-ink-muted mt-1">Kelola data pegawai, struktur organisasi, dan peran akses.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <a href="{{ route('admin.import.template', 'employees') }}" class="btn-action-secondary">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Template CSV
            </a>
            <button onclick="document.getElementById('importModal').classList.remove('hidden')" class="btn-action-success">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Upload CSV
            </button>
            <a href="{{ route('admin.employees.create') }}" class="btn-action-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Pegawai
            </a>
        </div>
    </div>

    {{-- Import Modal --}}
    <div id="importModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-100">
            <h3 class="text-lg font-extrabold text-ink mb-2">Upload Data Pegawai (CSV)</h3>
            <p class="text-xs text-ink-muted mb-4">Pastikan format sesuai dengan template CSV yang disediakan. Kolom <strong>nik, name, email, office_code, position_code, dan is_active</strong> wajib diisi. Kolom <strong>direct_supervisor_nik</strong> (NIK atasan langsung) dan <strong>manager_nik</strong> (NIK manajer) bersifat opsional.</p>
            <form action="{{ route('admin.import.store', 'employees') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
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

    {{-- Filter Form --}}
    <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('admin.employees.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Cari</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Nama, NIK, Email"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Kantor</label>
                <select name="office_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">Semua Kantor</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ ($filters['office_id'] ?? '') == $office->id ? 'selected' : '' }}>{{ $office->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Divisi</label>
                <select name="division_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">Semua Divisi</option>
                    @foreach($divisions as $division)
                        <option value="{{ $division->id }}" {{ ($filters['division_id'] ?? '') == $division->id ? 'selected' : '' }}>{{ $division->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn-action-primary w-full py-2.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c-4.97 0-9 4.03-9 9 0 2.12.74 4.07 1.97 5.61L4.35 20.35a1 1 0 0 0 1.41 1.41l2.74-2.74C10.04 19.53 11 19.8 12 19.8c4.97 0 9-4.03 9-9s-4.03-9-9-9z"/></svg>
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-navy-tint text-left text-[11px] font-bold text-ink-soft uppercase tracking-wider">
                        <th class="px-5 py-3">Nama / NIK</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Kantor</th>
                        <th class="px-4 py-3">Divisi</th>
                        <th class="px-4 py-3">Jabatan</th>
                        <th class="px-4 py-3">Atasan</th>
                        <th class="px-4 py-3">Manajer</th>
                        <th class="px-4 py-3 text-center">Akun</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-navy-tint/50 transition">
                            <td class="px-5 py-3.5">
                                <div class="font-bold text-ink">{{ $employee->name }}</div>
                                <div class="text-xs text-ink-muted font-mono">{{ $employee->nik ?? '—' }}</div>
                            </td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $employee->email }}</td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $employee->office?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $employee->division?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">
                                @if($employee->position)
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-[10px] bg-navy-tint text-navy px-1.5 py-0.5 rounded">{{ $employee->position->level }}</span>
                                        {{ $employee->position->name }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $employee->supervisor?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-ink-soft text-xs">{{ $employee->manager?->name ?? '—' }}</td>
                            <td class="px-4 py-3.5 text-center">
                                @if($employee->user)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Terhubung
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-slate-100 text-slate-500 ring-1 ring-slate-400/20">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                        Belum ada
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-1.5">
                                <a href="{{ route('admin.employees.edit', $employee) }}" class="btn-table-edit">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/></svg>
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="inline" onsubmit="return confirm('Hapus pegawai ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-table-delete">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-12 text-center text-ink-muted">Tidak ada pegawai yang ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-gray-100">{{ $employees->links() }}</div>
    </div>
</main>
@endsection
