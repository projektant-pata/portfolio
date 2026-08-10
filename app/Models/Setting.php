<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    public $timestamps = true;

    protected static function booted(): void
    {
        $flush = fn () => Cache::forget('settings.all');
        static::saved($flush);
        static::deleted($flush);
    }

    /**
     * All settings keyed by `key`, cached for the request lifecycle.
     *
     * @return array<string, array<string, string>>
     */
    public static function allKeyed(): array
    {
        $map = fn () => static::all()
            ->mapWithKeys(fn (Setting $s) => [$s->key => $s->value ?? []])
            ->all();

        if (config('app.debug')) {
            return $map();
        }

        return Cache::rememberForever('settings.all', $map);
    }

    /**
     * Localized value for a key, falling back to the `en` value, then the
     * given default. Never throws on a missing key.
     */
    public static function text(string $key, ?string $locale = null, string $default = ''): string
    {
        $locale ??= app()->getLocale();
        $value = static::allKeyed()[$key] ?? [];

        return $value[$locale] ?? $value['en'] ?? $default;
    }

    /**
     * Localized list value for a key (e.g. the rotating hero roles), falling
     * back to the `en` list, then an empty array.
     *
     * @return array<int, string>
     */
    public static function list(string $key, ?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $value = static::allKeyed()[$key] ?? [];

        return $value[$locale] ?? $value['en'] ?? [];
    }
}
