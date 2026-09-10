@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-4xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('periods.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Periode
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $period->exists ? 'Edit' : 'Tambah' }} Periode Penilaian</h1>
        <p class="text-sm text-ink-muted mt-1">Konfigurasi rentang waktu dan siklus evaluasi semesteran KPI 360°.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Periode</h2>
        </div>
        <form method="POST" action="{{ $period->exists ? route('periods.update', $period) : route('periods.store') }}" class="p-6 space-y-6">
            @csrf
            @if($period->exists) @method('PUT') @endif

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Periode</label>
                <input type="text" name="name" value="{{ old('name', $period->name) }}" placeholder="Contoh: Semester I Tahun 2025"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Tahun Evaluasi</label>
                    <input type="number" name="year" value="{{ old('year', $period->year ?? date('Y')) }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Semester</label>
                    <select name="semester" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        <option value="1" @selected(old('semester', $period->semester) == 1)>Semester 1 (Ganjil)</option>
                        <option value="2" @selected(old('semester', $period->semester) == 2)>Semester 2 (Genap)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $period->start_date?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $period->end_date?->format('Y-m-d')) }}"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Status Penilaian</label>
                <select name="status" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                    <option value="draft" @selected(old('status', $period->status ?? 'draft') === 'draft')>Draft (Persiapan)</option>
                    <option value="active" @selected(old('status', $period->status) === 'active')>Active (Sedang Berjalan)</option>
                    <option value="closed" @selected(old('status', $period->status) === 'closed')>Closed (Selesai/Ditutup)</option>
                </select>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('periods.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $period->exists ? 'Simpan Perubahan' : 'Buat Periode' }}
                </button>
            </div>
        </form>
    </div>
</main>
@endsection
