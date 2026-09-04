<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    protected static ?array $cachedSettings = null;

    public static function getAllSettings(): array
    {
        if (static::$cachedSettings !== null) {
            return static::$cachedSettings;
        }

        try {
            return static::$cachedSettings = cache()->remember('company_settings_all', 3600, function () {
                return static::pluck('value', 'key')->toArray();
            });
        } catch (\Throwable $e) {
            return static::$cachedSettings = static::pluck('value', 'key')->toArray();
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = static::getAllSettings();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        static::$cachedSettings = null;
        try {
            cache()->forget('company_settings_all');
        } catch (\Throwable $e) {
            // Ignore if cache unavailable
        }
    }
}
