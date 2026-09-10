<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppSetting;
use Illuminate\Database\Seeder;

class MailSettingSeeder extends Seeder
{
    /**
     * Seed konfigurasi mail default (Gmail SMTP).
     * Nilai password dikosongkan — admin wajib isi via UI.
     */
    public function run(): void
    {
        $defaults = [
            ['key' => 'mail_mailer',       'value' => 'smtp',            'label' => 'Mailer Driver',            'is_secret' => false],
            ['key' => 'mail_host',         'value' => 'smtp.gmail.com',  'label' => 'SMTP Host',                'is_secret' => false],
            ['key' => 'mail_port',         'value' => '587',             'label' => 'SMTP Port',                'is_secret' => false],
            ['key' => 'mail_encryption',   'value' => 'tls',             'label' => 'Enkripsi',                 'is_secret' => false],
            ['key' => 'mail_username',     'value' => '',                'label' => 'Username / Email',         'is_secret' => false],
            ['key' => 'mail_password',     'value' => '',                'label' => 'Password / App Password',  'is_secret' => true],
            ['key' => 'mail_from_address', 'value' => '',                'label' => 'Alamat Pengirim',          'is_secret' => false],
            ['key' => 'mail_from_name',    'value' => 'KPI 360 System',  'label' => 'Nama Pengirim',            'is_secret' => false],
        ];

        foreach ($defaults as $row) {
            AppSetting::query()->firstOrCreate(
                ['key' => $row['key']],
                [
                    'value' => $row['value'],
                    'label' => $row['label'],
                    'group' => 'mail',
                    'is_secret' => $row['is_secret'],
                ]
            );
        }
    }
}
