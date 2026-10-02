@extends('layouts.questionnaire')

@section('title', 'Verifikasi Identitas — Kuesioner KPI')

@push('head')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-up { animation: fadeInUp .45s ease-out both; }
    .animate-fade-up-delay-1 { animation: fadeInUp .45s .08s ease-out both; }
    .animate-fade-up-delay-2 { animation: fadeInUp .45s .16s ease-out both; }
    .animate-fade-up-delay-3 { animation: fadeInUp .45s .24s ease-out both; }
</style>
@endpush

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-slate-50 via-white to-navy-tint">
    <div class="w-full max-w-lg">

        {{-- Card utama --}}
        <div class="animate-fade-up bg-white rounded-3xl shadow-2xl shadow-navy/10 border border-slate-100 overflow-hidden">

            {{-- Header gradient navy --}}
            <div class="relative bg-gradient-to-br from-navy-deep via-navy to-navy-deep px-8 py-10 text-center overflow-hidden">
                {{-- Dekorasi lingkaran transparan --}}
                <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full bg-gold/10"></div>
                <div class="absolute -bottom-16 -left-10 w-52 h-52 rounded-full bg-white/5"></div>

                <div class="relative">
                    <div class="inline-flex items-center gap-2 bg-gold text-white text-[11px] font-bold tracking-[0.18em] uppercase px-3.5 py-1.5 rounded-full mb-5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        Verifikasi Aman
                    </div>

                    <h1 class="text-2xl font-extrabold text-white tracking-tight mb-2">
                        Pengisian Kuesioner KPI
                    </h1>
                    <p class="text-sm text-slate-300 leading-relaxed max-w-sm mx-auto">
                        Verifikasi identitas Anda untuk mengakses formulir kuesioner. Kode OTP akan dikirim ke email resmi terdaftar.
                    </p>
                </div>
            </div>

            {{-- Body form --}}
            <div class="px-8 py-8">

                {{-- Step indicator --}}
                <div class="animate-fade-up-delay-1 flex items-center justify-center gap-2 mb-7">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-gold text-white text-xs font-bold flex items-center justify-center">1</div>
                        <span class="text-xs font-bold text-ink">Verifikasi</span>
                    </div>
                    <div class="w-8 h-px bg-slate-200"></div>
                    <div class="flex items-center gap-2 opacity-50">
                        <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-500 text-xs font-bold flex items-center justify-center">2</div>
                        <span class="text-xs font-bold text-slate-400">Formulir</span>
                    </div>
                </div>

                {{-- Error --}}
                @if ($errors->any())
                    <div class="animate-fade-up-delay-1 mb-6 p-4 bg-rose-50 border border-rose-200 rounded-2xl flex items-start gap-3">
                        <svg class="w-5 h-5 text-rose-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-rose-800 leading-relaxed">{{ $errors->first() }}</p>
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('questionnaire.otp.request') }}" class="animate-fade-up-delay-2 space-y-6">
                    @csrf

                    <div>
                        <label for="nik" class="block text-xs font-bold uppercase tracking-[0.14em] text-ink-soft mb-2.5">
                            NIK Pegawai
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <input id="nik" type="text" name="nik" value="{{ old('nik') }}" required autofocus
                                placeholder="Masukkan NIK Anda"
                                class="w-full pl-12 pr-4 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm font-mono text-ink placeholder:text-slate-400 focus:ring-2 focus:ring-navy focus:border-navy focus:bg-white outline-none transition-all duration-200">
                        </div>
                        <p class="mt-2 text-[11px] text-slate-400">
                            NIK tercantum di kartu identitas atau email resmi kantor.
                        </p>
                    </div>

                    {{-- Info card --}}
                    <div class="bg-navy-tint rounded-2xl p-4 flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-navy flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gold" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <p class="text-xs text-ink-soft leading-relaxed">
                            Kode OTP 6 digit akan dikirim ke email terdaftar. Kode berlaku <strong class="text-navy-deep">10 menit</strong> dan hanya dapat digunakan <strong class="text-navy-deep">1 kali</strong>.
                        </p>
                    </div>

                    {{-- Submit button --}}
                    <button type="submit"
                        class="w-full py-4 bg-gradient-to-r from-navy-deep to-navy text-white text-sm font-bold rounded-2xl transition-all duration-200 hover:shadow-lg hover:shadow-navy/25 hover:-translate-y-0.5 active:translate-y-0 flex items-center justify-center gap-2.5">
                        Kirim Kode OTP ke Email
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>

                {{-- Footer --}}
                <div class="animate-fade-up-delay-3 mt-6 pt-6 border-t border-slate-100 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <p class="text-[11px] text-slate-400">
                        Dilindungi verifikasi dua langkah. Data Anda aman.
                    </p>
                </div>
            </div>
        </div>

        {{-- Footer kecil --}}
        <p class="animate-fade-up-delay-3 text-center text-[11px] text-slate-400 mt-6">
            &copy; {{ date('Y') }} {{ \App\Models\AppSetting::get('app_name', config('app.name', 'KPI 360')) }}
        </p>
    </div>
</div>
@endsection
