<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="0;url={{ route('login') }}">
    <title>{{ config('app.name', 'KPI 360') }}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:#16324f;font-family:ui-sans-serif,system-ui,sans-serif}
        .card{text-align:center;color:#fff}
        .logo{display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;background:#b98e1f;border-radius:14px;font-size:24px;font-weight:900;margin:0 auto 16px;letter-spacing:-1px}
        h1{font-size:1.25rem;font-weight:700;margin-bottom:6px}
        p{font-size:.875rem;opacity:.65}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">K</div>
        <h1>{{ config('app.name', 'KPI 360') }}</h1>
        <p>Mengalihkan ke halaman login…</p>
    </div>
</body>
</html>
