<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

final class MailSettingController extends Controller
{
    /**
     * Daftar key mail setting beserta metadata UI.
     *
     * @var array<string, array{label: string, type: string, placeholder: string, is_secret: bool}>
     */
    private const MAIL_KEYS = [
        'mail_mailer' => ['label' => 'Mailer Driver',       'type' => 'select',   'placeholder' => 'smtp',             'is_secret' => false],
        'mail_host' => ['label' => 'SMTP Host',           'type' => 'text',     'placeholder' => 'smtp.gmail.com',   'is_secret' => false],
        'mail_port' => ['label' => 'SMTP Port',           'type' => 'number',   'placeholder' => '587',              'is_secret' => false],
        'mail_encryption' => ['label' => 'Enkripsi',            'type' => 'select',   'placeholder' => 'tls',              'is_secret' => false],
        'mail_username' => ['label' => 'Username / Email',    'type' => 'email',    'placeholder' => 'akun@gmail.com',   'is_secret' => false],
        'mail_password' => ['label' => 'Password / App Password', 'type' => 'password', 'placeholder' => '••••••••',     'is_secret' => true],
        'mail_from_address' => ['label' => 'Alamat Pengirim',     'type' => 'email',    'placeholder' => 'noreply@gmail.com', 'is_secret' => false],
        'mail_from_name' => ['label' => 'Nama Pengirim',       'type' => 'text',     'placeholder' => 'KPI 360 System',   'is_secret' => false],
    ];

    /**
     * Halaman konfigurasi email (SMTP Gmail / mailer lain).
     */
    public function show(): View
    {
        $settings = AppSetting::query()
            ->where('group', 'mail')
            ->pluck('value', 'key');

        return view('admin.mail_setting.show', [
            'settings' => $settings,
            'mailKeys' => self::MAIL_KEYS,
        ]);
    }

    /**
     * Simpan konfigurasi mail ke app_settings dan terapkan ke runtime config.
     */
    public function save(Request $request): RedirectResponse
    {
        $rules = [
            'mail_mailer' => ['required', 'string', 'in:smtp,log,sendmail,ses,mailgun'],
            'mail_host' => ['required', 'string', 'max:255'],
            'mail_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'mail_encryption' => ['nullable', 'string', 'in:tls,ssl,'],
            'mail_username' => ['required', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ];

        $validated = $request->validate($rules);

        foreach ($validated as $key => $value) {
            $meta = self::MAIL_KEYS[$key];
            // Jangan timpa password dengan string kosong bila field dikosongkan.
            if ($meta['is_secret'] && ($value === null || $value === '')) {
                continue;
            }

            AppSetting::query()->updateOrCreate(
                ['key' => $key],
                [
                    'value' => $value,
                    'label' => $meta['label'],
                    'group' => 'mail',
                    'is_secret' => $meta['is_secret'],
                ]
            );
        }

        // Bersihkan cache semua mail key.
        foreach (array_keys(self::MAIL_KEYS) as $key) {
            Cache::forget("app_setting.{$key}");
        }

        return back()->with('success', 'Konfigurasi email berhasil disimpan.');
    }

    /**
     * Kirim email tes ke alamat yang ditentukan untuk verifikasi konfigurasi.
     */
    public function test(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        // Terapkan setting DB ke runtime config sebelum kirim.
        $this->applyMailConfig();

        try {
            Mail::raw('Ini adalah email tes dari sistem KPI 360. Konfigurasi SMTP Anda berhasil.', function ($message) use ($validated) {
                $message->to($validated['test_email'])->subject('Tes Konfigurasi Email — KPI 360');
            });

            return back()->with('success', 'Email tes berhasil dikirim ke '.$validated['test_email'].'.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal kirim email tes: '.$e->getMessage());
        }
    }

    /**
     * Terapkan konfigurasi mail dari DB ke runtime Laravel config.
     * Dipanggil sebelum kirim email bila tidak memakai middleware.
     */
    public static function applyMailConfig(): void
    {
        $map = [
            'mail_mailer' => 'mail.default',
            'mail_host' => 'mail.mailers.smtp.host',
            'mail_port' => 'mail.mailers.smtp.port',
            'mail_encryption' => 'mail.mailers.smtp.encryption',
            'mail_username' => 'mail.mailers.smtp.username',
            'mail_password' => 'mail.mailers.smtp.password',
            'mail_from_address' => 'mail.from.address',
            'mail_from_name' => 'mail.from.name',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = AppSetting::get($settingKey);
            if ($value !== null) {
                Config::set($configKey, $value);
            }
        }
    }
}
