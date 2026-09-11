<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — {{ \App\Models\AppSetting::get('app_name', config('app.name', 'KPI 360')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'ui-sans-serif', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'ui-monospace', 'monospace'],
                    },
                    colors: {
                        ink: { DEFAULT: '#16233a', soft: '#3d4c63', muted: '#7c8aa0' },
                        navy: { DEFAULT: '#16324f', deep: '#0f2438', tint: '#eef3f8' },
                        gold: { DEFAULT: '#b98e1f', soft: '#f6ecd4', deep: '#8a6a12' },
                        paper: '#f6f7f9',
                    },
                },
            },
        };
    </script>
</head>
<body class="bg-paper font-sans text-ink antialiased min-h-screen">
<main class="min-h-screen grid lg:grid-cols-2">

    {{-- ===== Panel Kiri: Brand & Konteks ===== --}}
    <section class="relative hidden lg:flex flex-col justify-between bg-navy-deep text-white p-12 overflow-hidden">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-white/5"></div>
        <div class="absolute -bottom-32 -left-16 w-96 h-96 rounded-full bg-gold/10 blur-2xl"></div>

        <div class="relative flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-gold/90 flex items-center justify-center font-extrabold text-navy-deep text-sm tracking-tight">360</div>
            <div class="leading-tight">
                <p class="font-bold text-white">KPI 360</p>
                <p class="text-[11px] text-white/50">SK No. 016/SK-Dir/BSB.02/XII/2024</p>
            </div>
        </div>

        <div class="relative max-w-md">
            <h1 class="text-4xl font-extrabold leading-tight tracking-tight">
                Sistem Penilaian<br>Kinerja <span class="text-gold">360 Derajat</span>
            </h1>
            <p class="mt-5 text-white/60 leading-relaxed text-sm">
                Evaluasi kinerja menyeluruh dari tiga perspektif — atasan langsung (P1),
                rekan sejawat (P2), dan bawahan langsung (P3) — lintas kantor maupun divisi.
            </p>
            <div class="mt-8 flex items-center gap-6 text-xs text-white/40">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Multi-Evaluator
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-sky-400"></span> Lintas Divisi
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gold"></span> Berbobot SK
                </div>
            </div>
        </div>

        <p class="relative text-[10px] text-white/30 leading-relaxed">
            Halaman ini direstriksi. Silakan masuk menggunakan akun yang terdaftar
            untuk mengakses dashboard rekapitulasi KPI.
        </p>
    </section>

    {{-- ===== Panel Kanan: Form Login ===== --}}
    <section class="flex items-center justify-center p-4 sm:p-12">
        <div class="w-full max-w-sm">
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl bg-navy-deep flex items-center justify-center font-extrabold text-gold text-sm">360</div>
                <p class="font-bold text-lg">KPI 360</p>
            </div>

            <h2 class="text-2xl font-extrabold tracking-tight">Selamat Datang</h2>
            <p class="mt-1.5 text-sm text-ink-muted">Masuk untuk mengelola penilaian kinerja.</p>

            @if ($errors->any())
                <div class="mt-6 flex items-start gap-2.5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-lg text-sm">
                    <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-ink-soft mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        autocomplete="email" placeholder="nama@perusahaan.co.id"
                        class="w-full px-4 py-3 bg-white border border-navy-tint rounded-lg text-sm placeholder:text-ink-muted/50 focus:ring-2 focus:ring-gold/60 focus:border-gold transition outline-none">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-ink-soft mb-1.5">Kata Sandi</label>
                    <input id="password" type="password" name="password" required
                        autocomplete="current-password" placeholder="••••••••"
                        class="w-full px-4 py-3 bg-white border border-navy-tint rounded-lg text-sm placeholder:text-ink-muted/50 focus:ring-2 focus:ring-gold/60 focus:border-gold transition outline-none">
                </div>

                <label class="flex items-center gap-2 select-none cursor-pointer">
                    <input type="checkbox" name="remember"
                        class="w-4 h-4 rounded border-navy-tint text-gold focus:ring-gold/60">
                    <span class="text-sm text-ink-soft">Ingat saya</span>
                </label>

                <button type="submit"
                    class="w-full py-3.5 bg-navy-deep hover:bg-navy text-white text-sm font-bold rounded-xl transition duration-200 shadow-md hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Masuk ke Dashboard
                </button>

                <div class="relative flex py-2 items-center">
                    <div class="flex-grow border-t border-slate-200"></div>
                    <span class="flex-shrink mx-3 text-[11px] uppercase tracking-wider text-ink-muted font-bold">atau</span>
                    <div class="flex-grow border-t border-slate-200"></div>
                </div>

                <a href="{{ route('questionnaire.start') }}"
                    class="w-full py-3.5 bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200/80 text-sm font-bold rounded-xl transition duration-200 shadow-sm hover:shadow hover:-translate-y-0.5 active:translate-y-0 active:scale-[0.98] flex items-center justify-center gap-2 text-center">
                    <svg class="w-5 h-5 text-amber-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Isi Kuisioner KPI (Verifikasi NIK &amp; OTP)
                </a>
            </form>

            <p class="mt-10 text-center text-[11px] text-ink-muted/70 leading-relaxed">
                Akses ditujukan bagi administrator sistem.<br>
                Pegawai mengisi KPI melalui tautan NIK &amp; token yang dikirim ke email.
            </p>
        </div>
    </section>
</main>
</body>
</html>
