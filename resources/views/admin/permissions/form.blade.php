@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-2xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.permissions.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Permission
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $permission->exists ? 'Edit' : 'Tambah' }} Permission</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola permission atomik untuk kontrol akses granular.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Permission</h2>
        </div>
        <form method="POST" action="{{ $permission->exists ? route('admin.permissions.update', $permission) : route('admin.permissions.store') }}" class="p-6 space-y-6">
            @csrf
            @if($permission->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Permission <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $permission->name) }}" placeholder="Contoh: Lihat Dashboard"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Slug <span class="text-rose-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $permission->slug) }}" placeholder="Contoh: dashboard.view"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                    <p class="text-xs text-ink-muted mt-1">Format: modul.action (contoh: employees.view)</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Modul <span class="text-rose-500">*</span></label>
                <input type="text" name="module" value="{{ old('module', $permission->module) }}" placeholder="Contoh: employees"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                <p class="text-xs text-ink-muted mt-1">Gunakan untuk mengelompokkan permission (employees, users, offices, divisions, positions, roles, permissions, kpi).</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Deskripsi fungsi permission ini"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">{{ old('description', $permission->description) }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.permissions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $permission->exists ? 'Simpan Perubahan' : 'Buat Permission' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
