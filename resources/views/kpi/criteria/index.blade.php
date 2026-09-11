@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0" x-data="{
    editCriteriaModal: false,
    editCriteriaData: { id: null, name: '', category: '', description: '' },
    editSubcriteriaModal: false,
    editSubcriteriaData: { id: null, name: '', weight: '', evaluator_type: 'P1', description: '' },
    openEditCriteria(c) {
        this.editCriteriaData = { id: c.id, name: c.name, category: c.category || '', description: c.description || '' };
        this.editCriteriaModal = true;
    },
    openEditSubcriteria(sc) {
        this.editSubcriteriaData = { id: sc.id, name: sc.name, weight: sc.weight, evaluator_type: sc.evaluator_type, description: sc.description || '' };
        this.editSubcriteriaModal = true;
    }
}">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Master Data Kinerja</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Manajemen Kriteria & Indikator</h1>
            <p class="text-sm text-ink-muted mt-1">Konfigurasi dinamis kriteria induk, indikator subkriteria, bobot nilai, dan tipe penilai (P1, P2, P3).</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button @click="document.getElementById('formAddCriteria').scrollIntoView({ behavior: 'smooth' })" class="btn-action-secondary">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Kriteria Induk
            </button>
            <button @click="document.getElementById('formAddSubcriteria').scrollIntoView({ behavior: 'smooth' })" class="btn-action-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Tambah Subkriteria
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Grid Form Tambah Kriteria & Subkriteria --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
        {{-- Form Tambah Kriteria Induk --}}
        <div id="formAddCriteria" class="lg:col-span-5 bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Tambah Kriteria Induk Baru
            </h2>
            <form method="POST" action="{{ route('criteria.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Kriteria Induk</label>
                    <input type="text" name="name" placeholder="Misal: Kompetensi Teknis" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Kategori / Kelompok</label>
                    <input type="text" name="category" placeholder="Misal: Hard Skill / Perilaku" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none">
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Deskripsi Singkat</label>
                    <textarea name="description" rows="2" placeholder="Penjelasan ruang lingkup kriteria..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none"></textarea>
                </div>
                <button type="submit" class="w-full btn-action-primary py-2.5">
                    Simpan Kriteria Induk
                </button>
            </form>
        </div>

        {{-- Form Tambah Subkriteria --}}
        <div id="formAddSubcriteria" class="lg:col-span-7 bg-white rounded-2xl shadow-card border border-slate-100 p-6">
            <h2 class="text-xs font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m-6 8h6"/></svg>
                Tambah Subkriteria / Indikator
            </h2>
            <form method="POST" action="{{ route('criteria.subcriteria.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Kriteria Induk</label>
                        <select name="criteria_id" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                            @foreach($criteria as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Tipe Penilai</label>
                        <select name="evaluator_type" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                            <option value="P1">P1 (Atasan Langsung)</option>
                            <option value="P2">P2 (Rekan Sejawat)</option>
                            <option value="P3">P3 (Bawahan Langsung)</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Indikator Subkriteria</label>
                        <input type="text" name="name" placeholder="Misal: Inisiatif & Kecepatan Kerja" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Bobot Nilai (%)</label>
                        <input type="number" name="weight" min="0" max="100" step="0.01" placeholder="15" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Deskripsi / Panduan Indikator (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Panduan rubrik atau rincian pengukuran indikator..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none"></textarea>
                </div>
                <button type="submit" class="w-full btn-action-success py-2.5">
                    Simpan Subkriteria
                </button>
            </form>
        </div>
    </div>

    {{-- Daftar Kriteria & Subkriteria --}}
    <div class="space-y-6">
        @foreach($criteria as $c)
            <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-navy/10 text-navy font-black text-sm flex items-center justify-center">{{ $loop->iteration }}</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-extrabold text-ink text-base">{{ $c->name }}</h3>
                                @if($c->category)
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-slate-200 text-ink-soft">{{ $c->category }}</span>
                                @endif
                            </div>
                            <p class="text-xs text-ink-muted mt-0.5">{{ $c->description ?? 'Aspek kompetensi penilaian kinerja' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-navy-tint text-navy">
                            {{ $c->subcriteria_count }} Indikator
                        </span>
                        <button type="button" @click="openEditCriteria({{ json_encode($c) }})" class="btn-table-edit">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            Edit
                        </button>
                        <form method="POST" action="{{ route('criteria.destroy', $c) }}" onsubmit="return confirm('Hapus kriteria induk ini beserta indikatornya?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-table-delete">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 text-[11px] font-bold uppercase tracking-wider text-ink-muted bg-white">
                                <th class="px-6 py-3">Nama Subkriteria / Indikator</th>
                                <th class="px-4 py-3 text-center">Tipe Penilai</th>
                                <th class="px-4 py-3 text-center">Bobot Relatif</th>
                                <th class="px-6 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($c->subcriteria as $sc)
                                <tr class="hover:bg-slate-50/60 transition">
                                    <td class="px-6 py-3.5 font-medium text-ink">
                                        {{ $sc->name }}
                                        @if($sc->description)
                                            <p class="text-[11px] text-ink-muted font-normal mt-0.5">{{ $sc->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center">
                                        @if($sc->evaluator_type === 'P1')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700 ring-1 ring-blue-600/20">P1 (Atasan)</span>
                                        @elseif($sc->evaluator_type === 'P2')
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700 ring-1 ring-purple-600/20">P2 (Rekan)</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-md text-xs font-bold bg-amber-50 text-amber-700 ring-1 ring-amber-600/20">P3 (Bawahan)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-center font-bold text-ink num">{{ number_format($sc->weight, 1) }}%</td>
                                    <td class="px-6 py-3.5 text-right space-x-1.5">
                                        <button type="button" @click="openEditSubcriteria({{ json_encode($sc) }})" class="btn-table-edit">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            Edit
                                        </button>
                                        <form method="POST" action="{{ route('criteria.subcriteria.destroy', $sc) }}" onsubmit="return confirm('Hapus subkriteria ini?')" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-table-delete">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-ink-muted">
                                        Belum ada subkriteria terdaftar pada kriteria ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modal Edit Kriteria Induk --}}
    <div x-show="editCriteriaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editCriteriaModal" x-transition.opacity class="fixed inset-0 bg-navy-deep/60 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="editCriteriaModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="'{{ url('criteria') }}/' + editCriteriaData.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-6 pb-4">
                        <h3 class="text-base font-bold text-ink mb-4">Edit Kriteria Induk</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Kriteria Induk</label>
                                <input type="text" name="name" x-model="editCriteriaData.name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy outline-none" required>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Kategori / Kelompok</label>
                                <input type="text" name="category" x-model="editCriteriaData.category" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Deskripsi</label>
                                <textarea name="description" rows="2" x-model="editCriteriaData.description" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-ink focus:border-navy outline-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="editCriteriaModal = false" class="btn-action-secondary">Batal</button>
                        <button type="submit" class="btn-action-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit Subkriteria --}}
    <div x-show="editSubcriteriaModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="editSubcriteriaModal" x-transition.opacity class="fixed inset-0 bg-navy-deep/60 transition-opacity"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div x-show="editSubcriteriaModal" x-transition class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form :action="'{{ url('criteria/subcriteria') }}/' + editSubcriteriaData.id" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-6 pt-6 pb-4">
                        <h3 class="text-base font-bold text-ink mb-4">Edit Subkriteria / Indikator</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Indikator Subkriteria</label>
                                <input type="text" name="name" x-model="editSubcriteriaData.name" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy outline-none" required>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Tipe Penilai</label>
                                    <select name="evaluator_type" x-model="editSubcriteriaData.evaluator_type" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy outline-none" required>
                                        <option value="P1">P1 (Atasan Langsung)</option>
                                        <option value="P2">P2 (Rekan Sejawat)</option>
                                        <option value="P3">P3 (Bawahan Langsung)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Bobot Nilai (%)</label>
                                    <input type="number" name="weight" min="0" max="100" step="0.01" x-model="editSubcriteriaData.weight" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-ink focus:border-navy outline-none" required>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Deskripsi / Catatan Panduan</label>
                                <textarea name="description" rows="2" x-model="editSubcriteriaData.description" class="w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-ink focus:border-navy outline-none"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="editSubcriteriaModal = false" class="btn-action-secondary">Batal</button>
                        <button type="submit" class="btn-action-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection
