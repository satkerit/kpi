<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class SystemSettingController extends Controller
{
    /**
     * Keys untuk System Settings.
     */
    private const SYSTEM_KEYS = [
        'app_name' => 'Nama Sistem',
        'app_slogan' => 'Slogan / Subjudul Sistem',
        'app_sk_number' => 'Nomor SK / Dasar Hukum',
        'app_region' => 'Region / Wilayah Kerja',
        'app_timezone' => 'Zona Waktu (Timezone)',
        'app_copyright' => 'Teks Hak Cipta (Copyright)',
        'app_contact_email' => 'Email Bantuan / Admin',
    ];

    /**
     * Halaman form Pengaturan Sistem (System Setup).
     */
    public function show(): View
    {
        $settings = AppSetting::query()
            ->where('group', 'system')
            ->pluck('value', 'key');

        return view('admin.system_setting.show', [
            'settings' => $settings,
            'systemKeys' => self::SYSTEM_KEYS,
        ]);
    }

    /**
     * Simpan / Perbarui Pengaturan Sistem.
     */
    public function save(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name' => ['required', 'string', 'max:255'],
            'app_slogan' => ['nullable', 'string', 'max:255'],
            'app_sk_number' => ['nullable', 'string', 'max:255'],
            'app_region' => ['nullable', 'string', 'max:255'],
            'app_timezone' => ['required', 'string', 'max:100'],
            'app_copyright' => ['nullable', 'string', 'max:255'],
            'app_contact_email' => ['nullable', 'email', 'max:255'],
            'app_logo_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        // Simpan teks pengaturan
        foreach (self::SYSTEM_KEYS as $key => $label) {
            if (isset($validated[$key])) {
                AppSetting::set($key, $validated[$key]);
            }
        }

        // Upload Logo bila diunggah
        if ($request->hasFile('app_logo_file')) {
            $file = $request->file('app_logo_file');
            $path = $file->store('system', 'public');
            $logoUrl = Storage::url($path);
            AppSetting::set('app_logo', $logoUrl);
        }

        // Bersihkan cache
        foreach (array_keys(self::SYSTEM_KEYS) as $key) {
            Cache::forget("app_setting.{$key}");
        }
        Cache::forget('app_setting.app_logo');

        return back()->with('success', 'Pengaturan Sistem berhasil disimpan.');
    }
}
