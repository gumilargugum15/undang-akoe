<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic key-value store (see the `group` column comment on the migration for the intended
 * scope: general, seo, social, mail, payment, ...). PaymentSettingService is the first — and so
 * far only — consumer, using the `payment.*` key namespace.
 */
class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, ?string $default = null): ?string
    {
        return static::query()->where('key', $key)->value('value') ?? $default;
    }

    public static function set(string $key, ?string $value, ?string $group = null): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }
}
