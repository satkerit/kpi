@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-3xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.divisions.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Divisi
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $record->exists ? 'Edit' : 'Tambah' }} Divisi</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola divisi organisasi beserta kepala divisinya.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Divisi</h2>
        </div>
        <form method="POST" action="{{ $record->exists ? route('admin.divisions.update', $record) : route('admin.divisions.store') }}" class="p-6 space-y-6">
            @csrf
            @if($record->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Divisi <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $record->name) }}" placeholder="Contoh: Divisi Teknologi Informasi"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kode Divisi <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $record->code) }}" placeholder="Contoh: DTI"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono uppercase text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kepala Divisi</label>
                <select name="head_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Kepala Divisi --</option>
                    @foreach($employees as $employee)
                        <option value="{{ $employee->id }}" {{ old('head_id', $record->head_id) == $employee->id ? 'selected' : '' }}>
                            {{ $employee->name }} ({{ $employee->nik }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-3 mt-2.5">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->is_active ?? true))
                    class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                <label class="text-sm text-ink-soft cursor-pointer select-none">Divisi aktif</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.divisions.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $record->exists ? 'Simpan Perubahan' : 'Buat Divisi' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection