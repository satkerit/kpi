@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-4xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Pegawai
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $employee->exists ? 'Edit' : 'Tambah' }} Pegawai</h1>
        <p class="text-sm text-ink-muted mt-1">Kelola data pegawai, struktur organisasi, dan peran akses.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Pegawai</h2>
        </div>
        <form method="POST" action="{{ $employee->exists ? route('admin.employees.update', $employee) : route('admin.employees.store') }}" class="p-6 space-y-6">
            @csrf
            @if($employee->exists) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" placeholder="Contoh: Budi Santoso"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Email <span class="text-rose-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}" placeholder="budi@company.com"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">NIK</label>
                    <input type="text" name="nik" value="{{ old('nik', $employee->nik) }}" placeholder="Contoh: 199001001"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $employee->phone) }}" placeholder="0812-3456-7890"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
            </div>

            @if(!$employee->exists)
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Password <span class="text-rose-500">*</span></label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                    <p class="text-xs text-ink-muted mt-1">Password akan di-hash otomatis (bcrypt).</p>
                </div>
            @else
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Password Baru (kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" placeholder="Minimal 8 karakter"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kantor</label>
                    <select name="office_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Kantor --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" {{ old('office_id', $employee->office_id) == $office->id ? 'selected' : '' }}>
                                {{ $office->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Divisi</label>
                    <select name="division_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Divisi --</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id) == $division->id ? 'selected' : '' }}>
                                {{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Bagian</label>
                    <select name="department_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Bagian --</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $employee->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Jabatan</label>
                    <select name="position_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Jabatan --</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                                [Lv.{{ $position->level }}] {{ $position->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Atasan Langsung</label>
                <select name="direct_supervisor_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Atasan --</option>
                    @foreach($supervisors as $sup)
                        <option value="{{ $sup->id }}" {{ old('direct_supervisor_id', $employee->direct_supervisor_id) == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->nik }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-ink-muted mt-1">Pegawai tidak bisa menjadi atasan dirinya sendiri.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Peran & Akses (Role)</label>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $role)
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="roles[]" value="{{ $role->slug }}" {{ in_array($role->slug, old('roles', $employee->getRoleSlugs())) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                            <span class="text-sm text-ink">{{ $role->name }}</span>
                        </label>
                    @endforeach
                </div>
                <p class="text-xs text-ink-muted mt-1">Pilih satu atau lebih role. Minimal 1 role untuk akses login.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.employees.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $employee->exists ? 'Simpan Perubahan' : 'Buat Pegawai' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
