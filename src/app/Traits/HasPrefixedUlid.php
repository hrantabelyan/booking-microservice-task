<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasPrefixedUlid
{
    /**
     * Boot the trait.
     */
    protected static function bootHasPrefixedUlid(): void
    {
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = static::generatePrefixedUlid();
            }
        });
    }

    /**
     * Generate a new ULID with the model's prefix.
     */
    public static function generatePrefixedUlid(): string
    {
        return Str::lower(static::getUlidPrefix().'_'.(string) Str::ulid());
    }

    /**
     * Get the prefix for the ULID.
     *
     * Override this method in your model to set a custom prefix. Defaults to the
     * lowercase plural form of the model name
     */
    protected static function getUlidPrefix(): string
    {
        return Str::snake(class_basename(static::class));
    }

    /**
     * Initialize the trait.
     *
     * Disable auto-incrementing as we're using ULID and set the ID type to string.
     * This is automatically executed when the trait is registered on the model.
     */
    public function initializeHasPrefixedUlid(): void
    {
        $this->incrementing = false;
        $this->keyType = 'string';
    }
}
