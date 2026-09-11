@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-4xl mx-auto w-full">
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
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                    Pegawai baru tidak memiliki akun login. Setelah pegawai disimpan, buat akun melalui menu <strong>Manajemen User</strong> dan kaitkan ke pegawai ini.
                </div>
            @else
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Akun Login Terkait</label>
                    @if($employee->user)
                        <div class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm text-ink">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="font-medium">{{ $employee->user->email }}</span>
                            <span class="text-xs text-ink-muted ml-auto">Kelola role & password di <a href="{{ route('admin.users.index') }}" class="text-navy hover:underline font-semibold">Manajemen User</a></span>
                        </div>
                    @else
                        <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-sm text-amber-700">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Belum ada akun login. Buat akun di <a href="{{ route('admin.users.index') }}" class="font-semibold hover:underline">Manajemen User</a> dan kaitkan ke pegawai ini.
                        </div>
                    @endif
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Kantor</label>
                    <select name="office_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">-- Pilih Kantor --</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" data-code="{{ $office->code }}" data-type="{{ $office->type }}" data-branch-code="{{ $office->branch_code }}"
                                {{ old('office_id', $employee->office_id) == $office->id ? 'selected' : '' }}>
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
                                {{ $division->parent_id ? '↳ ' : '' }}{{ $division->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
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
                <select name="direct_supervisor_id" id="direct_supervisor_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Atasan --</option>
                    @foreach($supervisors as $sup)
                        <option value="{{ $sup->id }}"
                            data-office="{{ $sup->office_id }}"
                            data-office-code="{{ $sup->office?->code }}"
                            data-office-type="{{ $sup->office?->type }}"
                            data-office-branch-code="{{ $sup->office?->branch_code }}"
                            data-division="{{ $sup->division_id }}"
                            {{ old('direct_supervisor_id', $employee->direct_supervisor_id) == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->nik }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-ink-muted mt-1">Hanya menampilkan pegawai di kantor/divisi yang sama. Untuk Kantor Kas, pilihan mencakup pegawai cabang induknya.</p>
            </div>

            <div>
                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Manajer</label>
                <select name="manager_id" id="manager_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Manajer --</option>
                    @foreach($managers as $mgr)
                        <option value="{{ $mgr->id }}"
                            data-office="{{ $mgr->office_id }}"
                            data-office-code="{{ $mgr->office?->code }}"
                            data-office-type="{{ $mgr->office?->type }}"
                            data-office-branch-code="{{ $mgr->office?->branch_code }}"
                            data-division="{{ $mgr->division_id }}"
                            {{ old('manager_id', $employee->manager_id) == $mgr->id ? 'selected' : '' }}>
                            {{ $mgr->name }} ({{ $mgr->nik }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-ink-muted mt-1">Manajer struktural di atas atasan langsung. Saringan kantor sama seperti pilihan atasan.</p>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('admin.employees.index') }}" class="btn-action-secondary">
                    Batal
                </a>
                <button type="submit" class="btn-action-primary">
                    {{ $employee->exists ? 'Simpan Perubahan' : 'Buat Pegawai' }}
                </button>
            </div>
        </form>
    </div>
</main>

<script>
(function () {
    const officeSelect   = document.querySelector('select[name="office_id"]');
    const divisionSelect = document.querySelector('select[name="division_id"]');

    // Semua dropdown pegawai yang di-filter: atasan langsung + manajer
    const peopleSelects = [
        document.getElementById('direct_supervisor_id'),
        document.getElementById('manager_id'),
    ].filter(Boolean);

    const optionSets = new Map(peopleSelects.map(sel => [
        sel,
        Array.from(sel.querySelectorAll('option:not([value=""])')),
    ]));

    function filterPeople() {
        const officeId   = officeSelect.value;
        const divisionId = divisionSelect.value;

        // Meta kantor terpilih — untuk Kas, sertakan pegawai cabang induknya
        const selectedOfficeOpt = officeSelect.selectedOptions[0];
        const selType = selectedOfficeOpt?.dataset.type ?? '';
        const selBranchCode = (selectedOfficeOpt?.dataset.branchCode ?? '').toUpperCase();

        optionSets.forEach((allOptions, peopleSelect) => {
            // Tidak ada filter dipilih — tampilkan semua
            if (! officeId && ! divisionId) {
                allOptions.forEach(opt => peopleSelect.appendChild(opt));
                return;
            }

            const currentVal = peopleSelect.value;
            allOptions.forEach(opt => opt.remove());

            allOptions.forEach(opt => {
                let matchOffice = true;
                if (officeId) {
                    const sameOffice = opt.dataset.office === officeId;
                    // Kasus Kas: boleh dari cabang induk (kode kantor atasan == branch_code Kas terpilih)
                    const parentBranch = selType === 'kas' && selBranchCode !== ''
                        && (opt.dataset.officeCode ?? '').toUpperCase() === selBranchCode;
                    matchOffice = sameOffice || parentBranch;
                }
                const matchDivision = ! divisionId || opt.dataset.division === divisionId;

                if (matchOffice && matchDivision) {
                    peopleSelect.appendChild(opt);
                }
            });

            // Pertahankan nilai sebelumnya jika masih ada di daftar
            peopleSelect.value = currentVal;
            if (peopleSelect.value !== currentVal) {
                peopleSelect.value = '';
            }
        });
    }

    officeSelect.addEventListener('change', filterPeople);
    divisionSelect.addEventListener('change', filterPeople);

    // Jalankan saat load agar sesuai dengan nilai awal (edit mode)
    filterPeople();
}());
</script>
@endsection
