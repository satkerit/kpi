@extends('layouts.questionnaire')

@section('title', 'Verifikasi OTP — KPI 360')
@section('period_label', 'Langkah 2 dari 2 · Masukkan Kode OTP')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-navy-deep flex items-center justify-center font-extrabold text-gold text-sm">360</div>
            <div>
                <h1 class="font-bold text-lg text-navy-deep">Verifikasi OTP</h1>
                <p class="text-xs text-ink-muted">Langkah 2 dari 2: Masukkan Kode OTP</p>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-5 p-3.5 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl text-sm">
                {{ session('status') }}
            </div>
        @endif

        @if (session('dev_otp'))
            <div class="mb-5 p-3.5 bg-amber-50 border border-amber-300 text-amber-900 rounded-xl text-sm font-mono">
                <span class="font-bold">[Dev] OTP:</span> {{ session('dev_otp') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-5 p-3.5 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <p class="text-sm text-ink-soft mb-6 leading-relaxed">
            Masukkan 6 digit kode OTP yang telah dikirim ke email terdaftar. Kode berlaku 10 menit.
        </p>

        <form method="POST" action="{{ route('questionnaire.verify') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="nik" value="{{ $nik }}">

            <div>
                <label for="otp" class="block text-xs font-bold uppercase tracking-wider text-ink-soft mb-2">Kode OTP</label>
                <input id="otp" type="text" name="otp" required autofocus
                    inputmode="numeric" maxlength="6" placeholder="6 digit kode"
                    class="w-full px-4 py-3 bg-paper border border-slate-200 rounded-xl text-center text-2xl font-mono tracking-[0.5em] focus:ring-2 focus:ring-navy focus:border-navy outline-none">
            </div>

            <button type="submit"
                class="w-full py-3.5 bg-navy hover:bg-navy-deep text-white text-sm font-bold rounded-xl transition shadow-card flex items-center justify-center gap-2">
                Verifikasi &amp; Masuk
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </button>

            <a href="{{ route('questionnaire.start') }}" class="block text-center text-xs text-ink-muted hover:text-navy font-semibold pt-2">
                &larr; Kirim ulang OTP / Ganti NIK
            </a>
        </form>
    </div>
</div>
@endsection
