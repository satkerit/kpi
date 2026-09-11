@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-4xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.roles.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Role
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $role->exists ? 'Edit' : 'Tambah' }} Role</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola peran akses dan permission yang melekat.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Role</h2>
        </div>
        <form method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}" class="p-6 space-y-6">
            @csrf
            @if($role->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Role <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" placeholder="Contoh: Administrator HR"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Slug <span class="text-rose-500">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $role->slug) }}" placeholder="Contoh: admin-hr"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                    <p class="text-xs text-ink-muted mt-1">Huruf kecil, angka, dash, underscore saja.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Deskripsi peran dan fungsi role ini"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">{{ old('description', $role->description) }}</textarea>
            </div>

            @if($role->exists && $role->is_system)
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-xl">
                    <p class="text-sm font-medium text-amber-800 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M12 5a9 9 0 0 1 0 18 9 9 0 0 1 0-18Z"/></svg>
                        Role sistem tidak bisa diubah permission-nya.
                    </p>
                </div>
            @else
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-3">Permission</label>
                    <div class="space-y-4 max-h-96 overflow-y-auto pr-2">
                        @php
                            $grouped = $permissions->groupBy('module');
                            $selected = $role->permissions->pluck('id')->toArray();
                        @endphp
                        @foreach($grouped as $module => $perms)
                            <div class="border border-slate-100 rounded-xl p-4">
                                <h4 class="text-xs font-bold text-ink-soft uppercase tracking-wider mb-3 text-gold-deep">{{ Str::upper($module) }}</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($perms as $perm)
                                        <label class="flex items-center gap-2 cursor-pointer select-none">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" {{ in_array($perm->id, $selected) ? 'checked' : '' }}
                                                class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                                            <span class="text-sm text-ink">{{ $perm->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.roles.index') }}" class="btn-action-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-action-primary">
                    {{ $role->exists ? 'Simpan Perubahan' : 'Buat Role' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
