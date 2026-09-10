<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengisian Kuesioner — Masukkan NIK</title>
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
<body class="bg-paper font-sans text-ink antialiased min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-navy-deep flex items-center justify-center font-extrabold text-gold text-sm">360</div>
            <div>
                <h1 class="font-bold text-lg text-navy-deep">Pengisian Kuesioner KPI</h1>
                <p class="text-xs text-ink-muted">Langkah 1 dari 2: Verifikasi Identitas</p>
            </div>
        </div>

        <p class="text-sm text-ink-soft mb-6 leading-relaxed">
            Masukkan Nomor Induk Karyawan (NIK). Kode OTP akan dikirimkan ke email resmi yang terdaftar pada data pegawai.
        </p>

        @if ($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('questionnaire.otp.request') }}" class="space-y-4">
            @csrf

            <div>
                <label for="nik" class="block text-xs font-bold uppercase tracking-wider text-ink-soft mb-2">NIK Pegawai</label>
                <input id="nik" type="text" name="nik" value="{{ old('nik') }}" required autofocus
                    placeholder="contoh: 199001012025011001"
                    class="w-full px-4 py-3 bg-paper border border-slate-200 rounded-xl text-sm font-mono focus:ring-2 focus:ring-navy focus:border-navy outline-none">
            </div>

            <button type="submit"
                class="w-full py-3.5 bg-navy hover:bg-navy-deep text-white text-sm font-bold rounded-xl transition shadow-card flex items-center justify-center gap-2">
                Kirim Kode OTP ke Email
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>

            <a href="{{ route('login') }}" class="block text-center text-xs text-ink-muted hover:text-navy font-semibold pt-2">
                &larr; Kembali ke Halaman Login
            </a>
        </form>
    </div>
</body>
</html>
