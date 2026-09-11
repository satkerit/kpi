<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    /**
     * Seed konfigurasi sistem default (Nama sistem, SK, logo, timezone, region).
     */
    public function run(): void
    {
        $defaults = [
            ['key' => 'app_name',          'value' => 'KPI 360',                                 'label' => 'Nama Sistem',               'is_secret' => false],
            ['key' => 'app_slogan',        'value' => 'Penilaian Kinerja 360°',                  'label' => 'Slogan / Subjudul Sistem',   'is_secret' => false],
            ['key' => 'app_sk_number',     'value' => 'SK Direksi No. 016/SK-Dir/BSB.02/XII/2024', 'label' => 'Nomor SK / Dasar Hukum',   'is_secret' => false],
            ['key' => 'app_region',        'value' => 'Indonesia (WIB)',                         'label' => 'Region / Wilayah Kerja',    'is_secret' => false],
            ['key' => 'app_timezone',      'value' => 'Asia/Jakarta',                            'label' => 'Zona Waktu (Timezone)',     'is_secret' => false],
            ['key' => 'app_logo',          'value' => '',                                        'label' => 'URL / Path File Logo',      'is_secret' => false],
            ['key' => 'app_copyright',     'value' => '© 2026 KPI 360 System. All rights reserved.', 'label' => 'Teks Hak Cipta (Copyright)', 'is_secret' => false],
            ['key' => 'app_contact_email', 'value' => 'admin@kpi360.co.id',                      'label' => 'Email Bantuan / Admin',    'is_secret' => false],
        ];

        foreach ($defaults as $row) {
            AppSetting::query()->firstOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'label' => $row['label'],
                    'group' => 'system',
                    'is_secret' => $row['is_secret'],
                ]
            );
        }
    }
}
