@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-3xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.evaluation-rules.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Setup Penilaian
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $record->exists ? 'Edit' : 'Tambah' }} Rule Penilaian</h1>
        <p class="text-sm text-ink-muted mt-1">P1 = atasan menilai bawahan · P2 = rekan sejawat · P3 = bawahan menilai atasan · SELF = penilaian diri sendiri.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Rule</h2>
        </div>
        <form method="POST" action="{{ $record->exists ? route('admin.evaluation-rules.update', $record) : route('admin.evaluation-rules.store') }}" class="p-6 space-y-6">
            @csrf
            @if($record->exists) @method('PUT') @endif

            @if($errors->any())
                <div class="rounded-xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Rule</label>
                    <input type="text" name="name" value="{{ old('name', $record->name) }}" placeholder="Contoh: Pincab nilai seluruh cabang"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Tipe Penilaian <span class="text-rose-500">*</span></label>
                    <select name="evaluator_type" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        <option value="P1" @selected(old('evaluator_type', $record->evaluator_type) === 'P1')>P1 — Atasan menilai bawahan</option>
                        <option value="P2" @selected(old('evaluator_type', $record->evaluator_type) === 'P2')>P2 — Rekan sejawat</option>
                        <option value="P3" @selected(old('evaluator_type', $record->evaluator_type) === 'P3')>P3 — Bawahan menilai atasan</option>
                        <option value="SELF" @selected(old('evaluator_type', $record->evaluator_type) === 'SELF')>SELF — Penilaian diri sendiri</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Jabatan Penilai <span class="text-rose-500">*</span></label>
                    <select name="evaluator_position_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        <option value="">— Pilih Jabatan —</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected((int) old('evaluator_position_id', $record->evaluator_position_id) === $position->id)>
                                {{ $position->name }} (Lv {{ $position->level }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Jabatan yang Dinilai</label>
                    <select name="evaluatee_position_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">— Semua (sesuai level) —</option>
                        @foreach($positions as $position)
                            <option value="{{ $position->id }}" @selected((int) old('evaluatee_position_id', $record->evaluatee_position_id) === $position->id)>
                                {{ $position->name }} (Lv {{ $position->level }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-ink-muted mt-1">Kosongkan untuk semua jabatan yang memenuhi syarat level P1/P2/P3.</p>
                </div>
            </div>

            <div>
                <p class="text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Lingkup Scope</p>
                <p class="text-[11px] text-ink-muted mb-3">Kosongkan semua untuk berlaku di seluruh kantor (lintas kantor). Isi untuk membatasi.</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Kantor</label>
                        <select name="scope_office_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                            <option value="">— Semua Kantor —</option>
                            @foreach($offices as $office)
                                <option value="{{ $office->id }}" @selected((int) old('scope_office_id', $record->scope_office_id) === $office->id)>{{ $office->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Divisi</label>
                        <select name="scope_division_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                            <option value="">— Semua Divisi —</option>
                            @foreach($divisions as $division)
                                <option value="{{ $division->id }}" @selected((int) old('scope_division_id', $record->scope_division_id) === $division->id)>{{ $division->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-soft mb-1.5">Bagian</label>
                        <select name="scope_department_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                            <option value="">— Semua Bagian —</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((int) old('scope_department_id', $record->scope_department_id) === $department->id)>{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Status</label>
                <label class="flex items-center gap-3 cursor-pointer select-none">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->is_active ?? true))
                        class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                    <span class="text-sm text-ink-soft">Rule aktif (diikutkan saat generate)</span>
                </label>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.evaluation-rules.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $record->exists ? 'Simpan Perubahan' : 'Buat Rule' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
