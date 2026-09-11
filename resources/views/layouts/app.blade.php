<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ \App\Models\AppSetting::get('app_name', config('app.name', 'KPI 360')) }} — {{ \App\Models\AppSetting::get('app_slogan', 'Penilaian Kinerja 360°') }}</title>
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
                        ink: { DEFAULT: '#1e293b', soft: '#475569', muted: '#94a3b8' },
                        navy: { DEFAULT: '#1e3a8a', deep: '#0f172a', tint: '#f1f5f9' },
                        gold: { DEFAULT: '#d97706', soft: '#fef3c7', deep: '#92400e' },
                        paper: '#f8fafc',
                    },
                    boxShadow: {
                        card: '0 1px 2px rgba(22,35,58,.06), 0 4px 16px rgba(22,35,58,.06)',
                        btn: '0 1px 2px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.12)',
                        'btn-hover': '0 4px 12px -2px rgba(15, 23, 42, 0.18)',
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

        /* Unified Premium Design System for Action Buttons */
        .btn-action-primary {
            display: inline-flex;
            items-center: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.08), 0 2px 6px -1px rgba(15, 23, 42, 0.12);
        }
        .btn-action-primary:hover {
            background-color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(15, 23, 42, 0.18);
        }
        .btn-action-primary:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-action-secondary {
            display: inline-flex;
            items-center: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #ffffff;
            color: #1e293b;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
            font-weight: 600;
            padding: 0.625rem 1.125rem;
            border-radius: 0.75rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        }
        .btn-action-secondary:hover {
            background-color: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px -1px rgba(15, 23, 42, 0.08);
        }
        .btn-action-secondary:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-action-success {
            display: inline-flex;
            items-center: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.625rem 1.125rem;
            border-radius: 0.75rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px rgba(5, 150, 105, 0.1), 0 2px 6px -1px rgba(5, 150, 105, 0.15);
        }
        .btn-action-success:hover {
            background-color: #047857;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(5, 150, 105, 0.25);
        }
        .btn-action-success:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-action-gold {
            display: inline-flex;
            items-center: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #d97706;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            padding: 0.625rem 1.125rem;
            border-radius: 0.75rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 1px 2px rgba(217, 119, 6, 0.1), 0 2px 6px -1px rgba(217, 119, 6, 0.15);
        }
        .btn-action-gold:hover {
            background-color: #b45309;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px -2px rgba(217, 119, 6, 0.25);
        }
        .btn-action-gold:active {
            transform: translateY(0) scale(0.98);
        }

        /* Table Action Buttons */
        .btn-table-edit {
            display: inline-flex;
            items-center: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #1e3a8a;
            background-color: #eff6ff;
            border: 1px solid #dbeafe;
            transition: all 0.15s ease;
        }
        .btn-table-edit:hover {
            background-color: #1e3a8a;
            color: #ffffff;
            border-color: #1e3a8a;
            transform: translateY(-1px);
        }

        .btn-table-delete {
            display: inline-flex;
            items-center: center;
            gap: 0.375rem;
            padding: 0.375rem 0.75rem;
            border-radius: 0.5rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #e11d48;
            background-color: #fff1f2;
            border: 1px solid #ffe4e6;
            transition: all 0.15s ease;
        }
        .btn-table-delete:hover {
            background-color: #e11d48;
            color: #ffffff;
            border-color: #e11d48;
            transform: translateY(-1px);
        }
    </style>
</head>
<body class="bg-paper font-sans text-ink antialiased min-h-screen">
<div class="min-h-screen lg:flex">
    <div class="lg:hidden sticky top-0 z-30 flex items-center justify-between gap-3 bg-navy-deep text-white px-4 py-3">
        <div class="flex items-center gap-2 min-w-0">
            @if($appLogo = \App\Models\AppSetting::get('app_logo'))
                <img src="{{ Storage::url($appLogo) }}" alt="Logo" class="w-9 h-9 rounded-xl object-contain bg-white/10 p-1 shrink-0">
            @else
                <div class="w-9 h-9 rounded-xl bg-gold text-navy-deep font-extrabold grid place-items-center shrink-0">
                    {{ strtoupper(mb_substr(\App\Models\AppSetting::get('app_name', 'KPI 360'), 0, 3)) }}
                </div>
            @endif
            <p class="text-sm font-bold truncate">{{ \App\Models\AppSetting::get('app_name', 'KPI 360') }}</p>
        </div>
        <button type="button" id="sidebarToggle" class="p-2 -mr-1 rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-gold" aria-label="Buka menu navigasi">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
    <div id="sidebarOverlay" class="hidden fixed inset-0 z-30 bg-navy-deep/60 lg:hidden"></div>
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 hidden w-64 shrink-0 bg-navy-deep text-white/90 flex-col lg:static lg:flex">
        <div class="flex items-center gap-3 px-6 h-16 border-b border-white/10">
            @if($appLogo = \App\Models\AppSetting::get('app_logo'))
                <img src="{{ Storage::url($appLogo) }}" alt="Logo" class="w-9 h-9 rounded-lg object-contain bg-white/10 p-1 shrink-0">
            @else
                <div class="w-9 h-9 rounded-lg bg-gold/90 flex items-center justify-center font-extrabold text-navy-deep text-sm tracking-tight shrink-0">
                    {{ strtoupper(mb_substr(\App\Models\AppSetting::get('app_name', 'KPI 360'), 0, 3)) }}
                </div>
            @endif
            <div class="leading-tight min-w-0">
                <p class="font-bold text-white text-sm truncate">{{ \App\Models\AppSetting::get('app_name', 'KPI 360') }}</p>
                <p class="text-[11px] text-white/50 truncate">{{ \App\Models\AppSetting::get('app_sk_number', 'SK 016/Dir/BSB.02') }}</p>
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
            <a href="{{ route('kpi.report') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('kpi.report') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3.75-12H18a2.25 2.25 0 0 1 2.25 2.25v11.25a2.25 2.25 0 0 1-2.25 2.25h-11.25A2.25 2.25 0 0 1 4.5 19.5V8.25A2.25 2.25 0 0 1 6.75 6h3.75c.621 0 1.125-.504 1.125-1.125V3.375C11.625 2.839 12.129 2.25 12.75 2.25h1.5c.621 0 1.125.589 1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                Laporan KPI
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
            <a href="{{ route('admin.positions.index') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.positions.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-3-3v6m-8 3h16a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2Z"/></svg>
                Jabatan
            </a>
            @if(Route::has('admin.system-settings.show'))
            <a href="{{ route('admin.system-settings.show') }}"
               class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition
                      {{ request()->routeIs('admin.system-settings.*') ? 'bg-white/10 text-white' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Setup Sistem
            </a>
            @endif
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
<script>
(function () {
    var btn = document.getElementById('sidebarToggle');
    var bar = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (!btn || !bar) { return; }
    function open() { bar.classList.remove('hidden'); bar.classList.add('flex'); if (ov) { ov.classList.remove('hidden'); } }
    function close() { if (window.innerWidth < 1024) { bar.classList.add('hidden'); bar.classList.remove('flex'); if (ov) { ov.classList.add('hidden'); } } }
    btn.addEventListener('click', function () { bar.classList.contains('hidden') ? open() : close(); });
    if (ov) { ov.addEventListener('click', close); }
})();
</script>
</body>
</html>
