@extends('layouts.app')

@section('content')
<main class="flex-1 px-4 sm:px-6 lg:px-10 py-6 sm:py-8 max-w-3xl mx-auto w-full">
    <div class="mb-8">
        <p class="text-[11px] font-bold tracking-[0.18em] text-gold-deep uppercase mb-1.5">Pengaturan Sistem</p>
        <h1 class="text-2xl font-extrabold text-ink tracking-tight">Konfigurasi Email (SMTP Gmail)</h1>
        <p class="text-sm text-ink-muted mt-1">Alamat email pengirim OTP / link verifikasi pegawai sebelum pengisian survei KPI.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 px-4 py-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm font-medium">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">SMTP Pengirim OTP</h2>
            <p class="text-xs text-ink-muted mt-0.5">Gunakan App Password Gmail (bukan password akun). Panduan: Akun Google → Keamanan → Verifikasi 2 langkah → Sandi aplikasi.</p>
        </div>
        <form method="POST" action="{{ route('admin.mail-settings.save') }}" class="p-6 space-y-5">
            @csrf

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
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Mailer Driver <span class="text-rose-500">*</span></label>
                    <select name="mail_mailer" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                        @foreach(['smtp', 'log', 'sendmail', 'ses', 'mailgun'] as $driver)
                            <option value="{{ $driver }}" @selected(old('mail_mailer', $settings['mail_mailer'] ?? 'smtp') === $driver)>{{ $driver }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Enkripsi</label>
                    <select name="mail_encryption" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                        <option value="">— tanpa enkripsi —</option>
                        <option value="tls" @selected(old('mail_encryption', $settings['mail_encryption'] ?? 'tls') === 'tls')>tls (port 587)</option>
                        <option value="ssl" @selected(old('mail_encryption', $settings['mail_encryption'] ?? '') === 'ssl')>ssl (port 465)</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">SMTP Host <span class="text-rose-500">*</span></label>
                    <input type="text" name="mail_host" value="{{ old('mail_host', $settings['mail_host'] ?? 'smtp.gmail.com') }}" placeholder="smtp.gmail.com"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">SMTP Port <span class="text-rose-500">*</span></label>
                    <input type="number" name="mail_port" value="{{ old('mail_port', $settings['mail_port'] ?? '587') }}" min="1" max="65535" placeholder="587"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Username / Email Gmail <span class="text-rose-500">*</span></label>
                    <input type="email" name="mail_username" value="{{ old('mail_username', $settings['mail_username'] ?? '') }}" placeholder="akun@gmail.com"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Password / App Password</label>
                    <input type="password" name="mail_password" value="" placeholder="{{ isset($settings['mail_password']) && $settings['mail_password'] ? '•••••••• (tersimpan — kosongkan bila tidak diubah)' : '••••••••' }}" autocomplete="new-password"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Alamat Pengirim <span class="text-rose-500">*</span></label>
                    <input type="email" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address'] ?? '') }}" placeholder="noreply@gmail.com"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-ink-soft uppercase tracking-wider mb-2">Nama Pengirim <span class="text-rose-500">*</span></label>
                    <input type="text" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name'] ?? 'KPI 360 System') }}" placeholder="KPI 360 System"
                        class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition" required>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 rounded-xl bg-navy text-white text-sm font-bold shadow hover:bg-navy-deep transition">
                    Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-2xl shadow-card border border-slate-100 overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-sm font-bold text-ink uppercase tracking-wider">Tes Pengiriman</h2>
            <p class="text-xs text-ink-muted mt-0.5">Kirim email tes memakai konfigurasi tersimpan.</p>
        </div>
        <form method="POST" action="{{ route('admin.mail-settings.test') }}" class="p-6 flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="email" name="test_email" value="{{ old('test_email') }}" placeholder="tujuan@contoh.com" required
                class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm text-ink focus:border-navy focus:ring-1 focus:ring-navy outline-none transition">
            <button type="submit" class="px-5 py-2.5 rounded-xl border border-slate-200 text-sm font-semibold text-ink-muted hover:bg-slate-50 transition">
                Kirim Email Tes
            </button>
        </form>
    </div>
</main>
@endsection
