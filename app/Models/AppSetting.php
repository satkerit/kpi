<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'label',
        'group',
        'is_secret',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
        ];
    }

    /**
     * Enkripsi nilai rahasia (password, app password) sebelum disimpan ke DB.
     * Mencegah kredensial SMTP terbaca langsung jika database bocor.
     */
    public function setValueAttribute(string $value): void
    {
        if ($this->is_secret && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Dekripsi nilai rahasia setelah dibaca dari DB.
     */
    public function getValueAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($this->is_secret) {
            try {
                return (string) Crypt::decryptString($value);
            } catch (\Throwable) {
                // Nilai lama yang tersimpan plaintext (sebelum enkripsi diterapkan) — kembalikan apa adanya.
                return $value;
            }
        }

        return $value;
    }

    /**
     * Ambil nilai setting berdasarkan key, dengan fallback ke .env / default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting.{$key}", 300, function () use ($key, $default) {
            $setting = static::query()->where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Simpan/perbarui setting dan bersihkan cache-nya.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("app_setting.{$key}");
    }
}
