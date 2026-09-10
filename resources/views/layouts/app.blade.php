<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'KPI 360') }} — Penilaian Kinerja 360°</title>
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
        [x-cloak] { display: none; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-thumb { background: #c9d2de; border-radius: 4px; }
        .num { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-paper font-sans text-ink antialiased min-h-screen">
<div class="flex min-h-screen">
    {{-- ===== Sidebar ===== --}}
    <aside class="hidden lg:flex flex-col w-64 shrink-0 bg-navy-deep text-white/90">
        <div class="flex items-center gap-3 px-6 h-16 border-b border-white/10">
            <div class="w-9 h-9 rounded-lg bg-gold/90 flex items-center justify-center font-extrabold text-navy-deep text-sm tracking-tight">360</div>
            <div class="leading-tight">
                <p class="font-bold text-white text-sm">KPI 360</p>
                <p class="text-[11px] text-white/50">SK 016/Dir/BSB.02</p>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            <span class="block px-3 pt-2 pb-1 text-[10px] font-bold tracking-widest text-white/30 uppercase">Penilaian</span>
            <a href="{{ route('kpi.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('kpi.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13h6V3H3v10Zm0 8h6v-5H3v5Zm12 0h6V11h-6v10Zm0-18v5h6V3h-6Z"/></svg>
                Dashboard Rekap
            </a>
            <a href="{{ route('assignments.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('assignments.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-6-2a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"/></svg>
                Penugasan Penilai
            </a>
            <span class="block px-3 pt-4 pb-1 text-[10px] font-bold tracking-widest text-white/30 uppercase">Master Data</span>
            <a href="{{ route('periods.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('periods.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M3 9h18M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"/></svg>
                Periode Penilaian
            </a>
            <a href="{{ route('criteria.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('criteria.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m-6 8h6"/></svg>
                Kriteria & Bobot
            </a>
            @if(auth()->user()?->hasRole('super-admin', 'admin-hr'))
            <span class="block px-3 pt-4 pb-1 text-[10px] font-bold tracking-widest text-white/30 uppercase">Manajemen Admin</span>
            <a href="{{ route('admin.employees.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.employees.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 20h5l-1.4-4.5a2 2 0 0 0-1.9-1.5h-.4M15 20H9m6 0-1-5m-5 5H4l1.4-4.5a2 2 0 0 1 1.9-1.5h.4M9 20l1-5m4 0-1.5-.5a2 2 0 0 1-1-1.7V12a3 3 0 0 0 6 0v-.8a2 2 0 0 1 1-1.7L19 9m-9 1 1.5-.5a2 2 0 0 0 1-1.7V7a3 3 0 0 1-6 0v-.8a2 2 0 0 0-1-1.7L5 4"/></svg>
                Manajemen Pegawai
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                Manajemen User
            </a>
            <a href="{{ route('admin.offices.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.offices.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M4 18h5v-6H4v6Zm6 0h4V4h-4v14Zm5 0h5v-9h-5v9Z"/></svg>
                Kantor
            </a>
            <a href="{{ route('admin.divisions.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.divisions.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                Divisi
            </a>
            <a href="{{ route('admin.departments.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.departments.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v4H4V5Zm0 12h16v4H4v-4Zm0-6h8v4H4v-4Zm12 0h4v4h-4v-4Z"/></svg>
                Bagian
            </a>
            <a href="{{ route('admin.positions.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.positions.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-8 3h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                Jabatan
            </a>
            <a href="{{ route('admin.evaluation-rules.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.evaluation-rules.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2M9 5a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2M9 5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2m-6 9 2 2 4-4"/></svg>
                Setup Penilaian
            </a>
            <a href="{{ route('admin.mail-settings.show') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.mail-settings.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                Setup Email OTP
            </a>
            <a href="{{ route('admin.roles.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.roles.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                Role & Permission
            </a>
            @endif
        </nav>
        <div class="mt-auto px-3 py-4 border-t border-white/10 space-y-3">
            @auth
                <div class="flex items-center gap-3 px-3 py-2 rounded-lg bg-white/5">
                    <div class="w-9 h-9 rounded-full bg-gold/90 text-navy-deep flex items-center justify-center font-bold text-sm shrink-0">
                        {{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div class="min-w-0 leading-tight">
                        <p class="text-sm font-semibold text-white truncate">{{ auth()->user()->name ?? 'Administrator' }}</p>
                        <p class="text-[11px] text-white/40 truncate">{{ auth()->user()->email ?? '' }}</p>
                    </div>
                </div>
            @endauth
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-white/60 hover:bg-rose-500/15 hover:text-rose-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-7.5A2.25 2.25 0 0 0 3.75 5.25v13.5A2.25 2.25 0 0 0 6 21h7.5a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ===== Main ===== --}}
    <div class="flex-1 flex flex-col min-w-0">
        @if(session('success'))
            <div x-data="" x-init="setTimeout(() => $el.remove(), 4000)" class="flex items-center gap-2 bg-emerald-50 border-b border-emerald-200 text-emerald-800 px-6 py-3 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="flex items-center gap-2 bg-rose-50 border-b border-rose-200 text-rose-800 px-6 py-3 text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </div>
</div>
</body>
</html>
