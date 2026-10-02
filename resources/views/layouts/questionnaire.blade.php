<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — {{ \App\Models\AppSetting::get('app_name', config('app.name', 'KPI 360 | BPRS Bangka Belitung')) }}</title>
    @vite('resources/css/app.css')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    @stack('head')
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased min-h-screen">
@yield('content')
@stack('scripts')
</body>
</html>
