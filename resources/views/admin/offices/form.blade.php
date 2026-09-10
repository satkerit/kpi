@extends('layouts.app')

@section('content')
<main class="flex-1 px-6 lg:px-10 py-8 max-w-3xl mx-auto w-full">
    <div class="mb-8">
        <a href="{{ route('admin.offices.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-ink-muted hover:text-navy uppercase tracking-wider mb-3 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Daftar Kantor
        </a>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">{{ $record->exists ? 'Edit' : 'Tambah' }} Kantor</h1>
        <p class="text-sm text-ink-muted mt-1">Data kantor digunakan sebagai lokasi penempatan pegawai lintas cabang.</p>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Formulir Kantor</h2>
        </div>
        <form method="POST" action="{{ $record->exists ? route('admin.offices.update', $record) : route('admin.offices.store') }}" class="p-6 space-y-6">
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
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Kantor <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $record->name) }}" placeholder="Contoh: Kantor Cabang Surabaya"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kode Kantor <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code', $record->code) }}" placeholder="Contoh: SBY"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono uppercase text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Jenis Kantor <span class="text-rose-500">*</span></label>
                    <select name="type" id="officeType"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        <option value="head_office" @selected(old('type', $record->type) === 'head_office')>Kantor Pusat</option>
                        <option value="branch"      @selected(old('type', $record->type) === 'branch')>Kantor Cabang</option>
                        <option value="kpo"         @selected(old('type', $record->type) === 'kpo')>KPO</option>
                        <option value="kas"         @selected(old('type', $record->type) === 'kas')>Kantor Kas</option>
                    </select>
                </div>
                <div id="branchCodeWrap">
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">
                        Kode Cabang Induk <span class="text-rose-500" id="branchRequired">*</span>
                    </label>
                    <input type="text" name="branch_code" id="branchCodeInput" value="{{ old('branch_code', $record->branch_code) }}"
                        placeholder="Kode kantor cabang induk"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-mono uppercase text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <p class="text-[11px] text-ink-muted mt-1">Wajib diisi jika jenis kantor adalah <strong>Kantor Kas</strong>.</p>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Alamat</label>
                <textarea name="address" rows="2" placeholder="Alamat lengkap kantor"
                    class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">{{ old('address', $record->address) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Telepon</label>
                    <input type="text" name="phone" value="{{ old('phone', $record->phone) }}" placeholder="Contoh: 021-5550123"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Status</label>
                    <label class="flex items-center gap-3 mt-2.5 cursor-pointer select-none">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $record->is_active ?? true))
                            class="w-4 h-4 rounded border-slate-300 text-gold focus:ring-gold/60">
                        <span class="text-sm text-ink-soft">Kantor aktif beroperasi</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.offices.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    {{ $record->exists ? 'Simpan Perubahan' : 'Buat Kantor' }}
                </button>
            </div>
        </form>
    </div>
</main>

<script>
(function () {
    const typeEl = document.getElementById('officeType');
    const inputEl = document.getElementById('branchCodeInput');
    const reqEl  = document.getElementById('branchRequired');

    function toggle() {
        const isKas = typeEl.value === 'kas';
        inputEl.required = isKas;
        reqEl.style.display = isKas ? '' : 'none';
    }

    typeEl.addEventListener('change', toggle);
    toggle();
})();
</script>
@endsection
