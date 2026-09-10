@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-3xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.departments.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Bagian
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $record->exists ? 'Edit' : 'Tambah' }} Bagian</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola bagian/departemen di bawah divisi dan kantor.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Bagian</h2>
        </div>
        <form method="POST" action="{{ $record->exists ? route('admin.departments.update', $record) : route('admin.departments.store') }}" class="p-6 space-y-6">
            @csrf
            @if($record->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Bagian <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $record->name) }}" placeholder="Contoh: Bagian Pengembangan Aplikasi"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kode Bagian <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $record->code) }}" placeholder="Contoh: BPA"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono uppercase text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Bagian Induk</label>
                    <select name="parent_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">— Bagian Utama (tanpa induk) —</option>
                        @foreach($parentDepartments as $parent)
                            @continue($record->exists && $parent->id === $record->id)
                            <option value="{{ $parent->id }}" {{ (int) old('parent_id', $record->parent_id) === $parent->id ? 'selected' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-muted mt-1">Kosongkan jika bagian tingkat atas. Pilih induk untuk membuat sub-bagian.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kategori Bagian</label>
                    <select name="category" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Kategori --</option>
                        <option value="operasional" {{ old('category', $record->category) === 'operasional' ? 'selected' : '' }}>Operasional</option>
                        <option value="bisnis" {{ old('category', $record->category) === 'bisnis' ? 'selected' : '' }}>Bisnis</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Divisi</label>
                    <select name="division_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id', $record->division_id) == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Level Kantor</label>
                    <select name="office_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Level Kantor --</option>
                        @foreach($offices->groupBy('type') as $type => $group)
                            @php
                                $typeLabel = match($type) {
                                    'head_office' => 'KPO / Kantor Pusat',
                                    'branch'      => 'Kantor Cabang',
                                    'kpo'         => 'KPO',
                                    'kas'         => 'Kantor Kas',
                                    default       => ucfirst($type),
                                };
                            @endphp
                            <optgroup label="{{ $typeLabel }}">
                                @foreach($group as $office)
                                    <option value="{{ $office->id }}" {{ old('office_id', $record->office_id) == $office->id ? 'selected' : '' }}>
                                        {{ $office->name }} ({{ strtoupper($office->type) }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Deskripsi</label>
                <textarea name="description" rows="2" placeholder="Deskripsi tugas dan fungsi bagian"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">{{ old('description', $record->description) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kepala Bagian</label>
                <select name="head_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Kepala Bagian --</option>
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
                <label class="text-sm text-ink-soft cursor-pointer select-none">Bagian aktif</label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.departments.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $record->exists ? 'Simpan Perubahan' : 'Buat Bagian' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
