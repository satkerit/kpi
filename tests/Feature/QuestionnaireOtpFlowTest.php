<?php

namespace Tests\Feature;

use App\Domains\HumanResource\Models\Employee;
use App\Domains\MasterKpi\Models\KpiPeriod;
use App\Mail\OtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Alur kuesioner KPI untuk PEGAWAI TANPA AKUN LOGIN.
 *
 * Mengunci dua regresi yang pernah berulang:
 *  1. Halaman kuesioner tidak boleh memuat Tailwind lewat CDN production.
 *  2. Pegawai dengan sesi OTP saja TIDAK BOLEH terlempar ke halaman login
 *     (route kpi.* berada di balik middleware 'auth').
 */
final class QuestionnaireOtpFlowTest extends TestCase
{
    use RefreshDatabase;

    private function makePeriod(): KpiPeriod
    {
        return KpiPeriod::query()->create([
            'name' => 'Periode Kinerja 2026',
            'year' => 2026,
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'status' => 'active',
            'settings' => ['allow_not_observed' => true],
        ]);
    }

    private function makeEmployee(string $nik, string $email, array $extra = []): Employee
    {
        return Employee::query()->create(array_merge([
            'nik' => $nik,
            'name' => 'Pegawai Uji '.$nik,
            'email' => $email,
            'is_active' => true,
        ], $extra));
    }

    /**
     * Jalankan seluruh alur: minta OTP -> verifikasi ->ISTERi challenge_id.
     */
    private function passOtpFlow(Employee $employee): void
    {
        Mail::fake();

        // Muka formulir NIK dulu, seperti yang dilakukan browser, agar session + token CSRF terbentuk.
        $this->get(route('questionnaire.start'))->assertOk();

        $this->post(route('questionnaire.otp.request'), [
            '_token' => csrf_token(),
            'nik' => $employee->nik,
        ])->assertRedirect(route('questionnaire.verify.form'));

        $otp = null;
        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$otp) {
            $otp = $mail->code;

            return true;
        });

        $challengeId = session('otp_challenge_id');
        $this->assertNotEmpty($challengeId, 'challenge_id harus tersimpan di session');

        // requestOtp() memanggil session()->regenerateToken(), jadi token diambil ulang.
        $this->get(route('questionnaire.verify.form'))->assertOk();

        $this->post(route('questionnaire.verify'), [
            '_token' => csrf_token(),
            'nik' => $employee->nik,
            'challenge_id' => $challengeId,
            'otp' => $otp,
        ])->assertRedirect(route('questionnaire.form'));
    }

    public function test_halaman_kuesioner_tidak_memuat_tailwind_cdn(): void
    {
        $response = $this->get(route('questionnaire.start'));

        $response->assertOk();
        $response->assertDontSee('cdn.tailwindcss.com', false);
    }

    public function test_halaman_login_tidak_memuat_tailwind_cdn(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertDontSee('cdn.tailwindcss.com', false);
    }

    public function test_pegawai_tanpa_akun_login_dapat_membuka_form_setelah_otp(): void
    {
        $this->makePeriod();
        $employee = $this->makeEmployee('3201010101990001', 'pegawai@kpi360.test');

        $this->passOtpFlow($employee);

        $response = $this->get(route('questionnaire.form'));

        $response->assertOk();
        $this->assertFalse(
            auth()->check(),
            'Sesi OTP tidak boleh sekaligus membuat user terautentikasi pada web guard.'
        );
    }

    public function test_kode_otp_salah_ditolak_dan_tidak_membuka_form(): void
    {
        $this->makePeriod();
        $employee = $this->makeEmployee('3201010101990002', 'pegawai2@kpi360.test');

        Mail::fake();

        $this->get(route('questionnaire.start'))->assertOk();

        $this->post(route('questionnaire.otp.request'), [
            '_token' => csrf_token(),
            'nik' => $employee->nik,
        ])->assertRedirect(route('questionnaire.verify.form'));

        $challengeId = session('otp_challenge_id');
        $this->get(route('questionnaire.verify.form'))->assertOk();

        $this->post(route('questionnaire.verify'), [
            '_token' => csrf_token(),
            'nik' => $employee->nik,
            'challenge_id' => $challengeId,
            'otp' => '000000',
        ])->assertSessionHasErrors('otp');

        $this->get(route('questionnaire.form'))
            ->assertRedirect(route('questionnaire.start'));
    }

    public function test_nik_tidak_terdaftar_tidak_kirim_otp(): void
    {
        $this->makePeriod();
        Mail::fake();

        $this->get(route('questionnaire.start'))->assertOk();

        $this->post(route('questionnaire.otp.request'), [
            '_token' => csrf_token(),
            'nik' => '9999999999999999',
        ])->assertSessionHasErrors('nik');

        Mail::assertNothingSent();
    }

    public function test_tanpa_periode_kuesioner_tidak_melempar_ke_halaman_login(): void
    {
        // Sengaja TIDAK membuat periode KPI.
        $employee = $this->makeEmployee('3201010101990003', 'pegawai3@kpi360.test');

        $this->passOtpFlow($employee);

        // Regresi lama: redirect ke route('kpi.index') → middleware 'auth' → /login.
        $this->get(route('questionnaire.form'))
            ->assertRedirect(route('questionnaire.start'));
    }

    public function test_form_menampilkan_subkriteria_penilaian(): void
    {
        $this->makePeriod();

        $supervisor = $this->makeEmployee('3201010101990010', 'atasan@kpi360.test');
        $employee = $this->makeEmployee('3201010101990004', 'pegawai4@kpi360.test', [
            'direct_supervisor_id' => $supervisor->id,
        ]);

        $this->passOtpFlow($employee);

        $response = $this->get(route('questionnaire.form'));

        $response->assertOk();
        $response->assertSee($supervisor->name);
        $response->assertSee('Periode Kinerja 2026');
    }
}