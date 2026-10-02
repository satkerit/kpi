<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ \App\Models\AppSetting::get('app_name', config('app.name', 'KPI 360 | BPRS Bangka Belitung')) }}</title>
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
    @stack('head')
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen">
@yield('content')
</body>
</html>
