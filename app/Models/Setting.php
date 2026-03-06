<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get a setting value by key with optional default.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $settings = Cache::rememberForever('settings', function (): array {
            return self::pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }

    /**
     * Set multiple settings at once.
     *
     * @param  array<string, string|null>  $settings
     */
    public static function setMany(array $settings): void
    {
        DB::transaction(function () use ($settings): void {
            foreach ($settings as $key => $value) {
                self::updateOrCreate(['key' => $key], ['value' => $value]);
            }
        });

        Cache::forget('settings');
    }
}
