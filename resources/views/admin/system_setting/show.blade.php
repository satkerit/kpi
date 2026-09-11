@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 w-full min-w-0" x-data="{
    logoPreview: '{{ App\Models\AppSetting::get('app_logo') ? asset(App\Models\AppSetting::get('app_logo')) : '' }}',
    fileChosen(event) {
        let file = event.target.files[0];
        if (file) {
            let reader = new FileReader();
            reader.onload = (e) => {
                this.logoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    }
}">
    <div class="flex flex-wrap items-end justify-between gap-4 mb-8">
        <div>
            <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Konfigurasi Aplikasi</p>
            <h1 class="text-2xl font-extrabold text-ink tracking-tight">Pengaturan Sistem (System Setup)</h1>
            <p class="text-sm text-ink-muted mt-1">Sesuaikan identitas instansi, nama sistem, logo resmi, zona waktu (region), nomor SK, dan kontak dukungan.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('kpi.index') }}" class="btn-action-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
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

    <form method="POST" action="{{ route('admin.system-settings.save') }}" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            {{-- Kolom Kiri: Form Detail Identitas & Pengaturan --}}
            <div class="lg:col-span-8 space-y-6">
                {{-- Card 1: Identitas Aplikasi --}}
                <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
                    <h2 class="text-sm font-bold text-ink uppercase tracking-wider mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Identitas & Penamaan Sistem
                    </h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nama Sistem / Aplikasi <span class="text-rose-500">*</span></label>
                                <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'KPI 360') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="Contoh: KPI 360" required>
                                <p class="text-[11px] text-ink-muted mt-1">Nama yang tampil pada judul tab browser, sidebar, dan header.</p>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Slogan / Subjudul</label>
                                <input type="text" name="app_slogan" value="{{ old('app_slogan', $settings['app_slogan'] ?? 'Penilaian Kinerja 360°') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="Contoh: Penilaian Kinerja 360°">
                                <p class="text-[11px] text-ink-muted mt-1">Subjudul atau keterangan di halaman login.</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Nomor SK / Dasar Hukum Pelaksanaan</label>
                            <input type="text" name="app_sk_number" value="{{ old('app_sk_number', $settings['app_sk_number'] ?? 'SK Direksi No. 016/SK-Dir/BSB.02/XII/2024') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="Contoh: SK Direksi No. 016/SK-Dir/BSB.02/XII/2024">
                            <p class="text-[11px] text-ink-muted mt-1">Dicantumkan pada sidebar bawah logo dan judul form kuesioner.</p>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Region & Waktu --}}
                <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
                    <h2 class="text-sm font-bold text-ink uppercase tracking-wider mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Region & Pengaturan Waktu
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Region / Wilayah Kerja</label>
                            <input type="text" name="app_region" value="{{ old('app_region', $settings['app_region'] ?? 'Indonesia (WIB)') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="Contoh: Indonesia (WIB)">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Zona Waktu (Timezone) <span class="text-rose-500">*</span></label>
                            <select name="app_timezone" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" required>
                                <option value="Asia/Jakarta" @selected(($settings['app_timezone'] ?? 'Asia/Jakarta') === 'Asia/Jakarta')>Asia/Jakarta (WIB, UTC+7)</option>
                                <option value="Asia/Makassar" @selected(($settings['app_timezone'] ?? '') === 'Asia/Makassar')>Asia/Makassar (WITA, UTC+8)</option>
                                <option value="Asia/Jayapura" @selected(($settings['app_timezone'] ?? '') === 'Asia/Jayapura')>Asia/Jayapura (WIT, UTC+9)</option>
                                <option value="Asia/Bangkok" @selected(($settings['app_timezone'] ?? '') === 'Asia/Bangkok')>Asia/Bangkok (UTC+7)</option>
                                <option value="UTC" @selected(($settings['app_timezone'] ?? '') === 'UTC')>UTC</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Card 3: Kontak & Copyright --}}
                <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
                    <h2 class="text-sm font-bold text-ink uppercase tracking-wider mb-5 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gold-deep" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Kontak Dukungan & Footer
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Email Dukungan / Admin</label>
                            <input type="email" name="app_contact_email" value="{{ old('app_contact_email', $settings['app_contact_email'] ?? 'admin@kpi360.co.id') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="admin@kpi360.co.id">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-1.5">Teks Hak Cipta (Copyright)</label>
                            <input type="text" name="app_copyright" value="{{ old('app_copyright', $settings['app_copyright'] ?? '© 2026 KPI 360 System. All rights reserved.') }}" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none" placeholder="© 2026 KPI 360 System. All rights reserved.">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Upload & Preview Logo + Action Card --}}
            <div class="lg:col-span-4 space-y-6">
                <div class="bg-white rounded-2xl shadow-card border border-slate-100 p-6">
                    <h2 class="text-sm font-bold text-ink uppercase tracking-wider mb-4 flex items-center gap-2">
                        <svg class="w-4 h-4 text-navy" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Logo Sistem / Instansi
                    </h2>
                    
                    {{-- Live Preview Box --}}
                    <div class="mb-5 flex flex-col items-center justify-center p-6 rounded-2xl bg-slate-50 border-2 border-dashed border-slate-200 text-center">
                        <template x-if="logoPreview">
                            <div class="space-y-3">
                                <img :src="logoPreview" alt="Logo Preview" class="max-h-24 max-w-full object-contain mx-auto rounded-lg shadow-sm">
                                <span class="inline-block px-2.5 py-1 rounded text-[11px] font-bold text-emerald-700 bg-emerald-50">Logo Terpasang</span>
                            </div>
                        </template>
                        <template x-if="!logoPreview">
                            <div class="space-y-2 py-4">
                                <div class="w-16 h-16 rounded-2xl bg-gold/90 text-navy-deep font-black text-xl flex items-center justify-center mx-auto shadow">
                                    360
                                </div>
                                <p class="text-xs text-ink-muted">Menggunakan logo teks bawaan (Default)</p>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Unggah File Logo Baru</label>
                        <input type="file" name="app_logo_file" @change="fileChosen" accept="image/*" class="w-full text-xs text-ink-soft file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-navy file:text-white hover:file:bg-navy-deep cursor-pointer">
                        <p class="text-[11px] text-ink-muted mt-2 leading-relaxed">Format: PNG, JPG, SVG, WebP. Disarankan proporsi transparan dengan resolusi min. 200x200 px (Maks 2MB).</p>
                    </div>
                </div>

                {{-- Card Simpan Perubahan --}}
                <div class="bg-gradient-to-br from-navy to-navy-deep rounded-2xl p-6 text-white shadow-xl">
                    <h3 class="text-base font-bold mb-2">Simpan Perubahan</h3>
                    <p class="text-xs text-white/70 mb-5 leading-relaxed">Perubahan nama sistem, nomor SK, logo, dan region akan langsung diterapkan ke seluruh tampilan pengguna dan laporan.</p>
                    <button type="submit" class="w-full py-3 rounded-xl bg-gold hover:bg-gold-deep text-navy-deep hover:text-white font-extrabold text-sm transition duration-200 flex items-center justify-center gap-2 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Simpan Semua Pengaturan
                    </button>
                </div>
            </div>
        </div>
    </form>
</main>
@endsection
