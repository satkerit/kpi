@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-2xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar User
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">Edit Akun User</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola data akun login dan peran akses.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir User</h2>
        </div>
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="p-6 space-y-6">
            @csrf @method('PUT')

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" placeholder="Contoh: Budi Santoso"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" placeholder="budi@company.com"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Password Baru (kosongkan jika tidak diubah)</label>
                <input type="password" name="password" placeholder="Minimal 8 karakter"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Peran & Akses (Role) <span class="text-rose-500">*</span></label>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="roles[]" value="{{ $role->slug }}" {{ in_array($role->slug, old('roles', $user->getRoleSlugs())) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                            <span class="text-sm text-ink">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-ink-muted mt-1">Pilih minimal 1 role untuk akses login.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</main>
@endsection