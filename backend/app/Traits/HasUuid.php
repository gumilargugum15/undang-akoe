<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Stamps a `uuid` column on creation. For models needing extra creation-time
 * behavior (e.g. side effects, notifications), use an Observer instead — see
 * UserObserver. This trait exists purely to avoid repeating the same
 * boot-closure across every model that only needs a public identifier.
 */
trait HasUuid
{
    protected static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }
}
