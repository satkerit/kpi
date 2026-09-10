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
                <select name="direct_supervisor_id" id="direct_supervisor_id" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                    <option value="">-- Pilih Atasan --</option>
                    @foreach($supervisors as $sup)
                        <option value="{{ $sup->id }}"
                            data-office="{{ $sup->office_id }}"
                            data-division="{{ $sup->division_id }}"
                            data-department="{{ $sup->department_id }}"
                            {{ old('direct_supervisor_id', $employee->direct_supervisor_id) == $sup->id ? 'selected' : '' }}>
                            {{ $sup->name }} ({{ $sup->nik }})
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-ink-muted mt-1">Hanya menampilkan pegawai di kantor/divisi/bagian yang sama. Pilih kantor/divisi/bagian terlebih dahulu.</p>
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

<script>
(function () {
    const officeSelect     = document.querySelector('select[name="office_id"]');
    const divisionSelect   = document.querySelector('select[name="division_id"]');
    const departmentSelect = document.querySelector('select[name="department_id"]');
    const supervisorSelect = document.getElementById('direct_supervisor_id');

    // Simpan semua opsi asli kecuali placeholder
    const allOptions = Array.from(supervisorSelect.querySelectorAll('option:not([value=""])'));

    function filterSupervisors() {
        const officeId     = officeSelect.value;
        const divisionId   = divisionSelect.value;
        const departmentId = departmentSelect.value;

        // Tidak ada filter dipilih — tampilkan semua
        if (! officeId && ! divisionId && ! departmentId) {
            allOptions.forEach(opt => supervisorSelect.appendChild(opt));
            return;
        }

        const currentVal = supervisorSelect.value;

        // Hapus semua opsi non-placeholder
        allOptions.forEach(opt => opt.remove());

        // Tambahkan kembali yang cocok minimal satu kriteria yang dipilih
        allOptions.forEach(opt => {
            const matchOffice     = ! officeId     || opt.dataset.office     === officeId;
            const matchDivision   = ! divisionId   || opt.dataset.division   === divisionId;
            const matchDepartment = ! departmentId || opt.dataset.department === departmentId;

            if (matchOffice && matchDivision && matchDepartment) {
                supervisorSelect.appendChild(opt);
            }
        });

        // Pertahankan nilai sebelumnya jika masih ada di daftar
        supervisorSelect.value = currentVal;
        if (supervisorSelect.value !== currentVal) {
            supervisorSelect.value = '';
        }
    }

    officeSelect.addEventListener('change', filterSupervisors);
    divisionSelect.addEventListener('change', filterSupervisors);
    departmentSelect.addEventListener('change', filterSupervisors);

    // Jalankan saat load agar sesuai dengan nilai awal (edit mode)
    filterSupervisors();
}());
</script>
@endsection
