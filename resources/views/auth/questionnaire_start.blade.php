@extends('layouts.questionnaire')

@section('title', 'Pengisian Kuesioner — Masukkan NIK')
@section('period_label', 'Langkah 1 dari 2 · Verifikasi Identitas')

@section('content')
<div class="flex justify-center">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl border border-slate-100 p-6 sm:p-8">
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
</div>
@endsection
