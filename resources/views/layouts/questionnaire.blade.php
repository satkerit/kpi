<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kuesioner KPI 360°')</title>
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
                    boxShadow: {
                        card: '0 1px 2px rgba(22,35,58,.06), 0 4px 16px rgba(22,35,58,.06)',
                    },
                },
            },
        };
    </script>
    <style>
        .num { font-variant-numeric: tabular-nums; }
    </style>
    @stack('styles')
</head>
<body class="bg-paper font-sans text-ink antialiased min-h-screen">
    {{-- Header kuesioner: brand + info penilai --}}
    <header class="bg-navy-deep text-white sticky top-0 z-30 shadow-lg">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-xl bg-gold flex items-center justify-center font-extrabold text-navy-deep text-sm shrink-0">360</div>
                <div class="min-w-0">
                    <p class="font-bold text-sm sm:text-base truncate">Kuesioner KPI 360°</p>
                    <p class="text-[11px] sm:text-xs text-white/60 truncate">@yield('period_label', 'Penilaian Kinerja')</p>
                </div>
            </div>
            @hasSection('evaluator')
                <div class="text-right shrink-0">
                    <p class="text-[11px] text-white/50 uppercase tracking-wider font-semibold">Penilai</p>
                    <p class="text-xs sm:text-sm font-bold truncate max-w-[160px] sm:max-w-none">@yield('evaluator')</p>
                </div>
            @endif
        </div>
        @hasSection('progress')
            <div class="h-1 bg-white/10">
                <div class="h-full bg-gold transition-all duration-500" style="width: @yield('progress')%"></div>
            </div>
        @endif
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 py-6 sm:py-8 w-full">
        @yield('content')
    </main>

    <footer class="max-w-5xl mx-auto px-4 sm:px-6 pb-8 text-center">
        <p class="text-[11px] text-ink-muted">Penilaian bersifat rahasia. Jawaban Anda hanya digunakan untuk pengembangan kinerja.</p>
    </footer>

    @stack('scripts')
</body>
</html>
