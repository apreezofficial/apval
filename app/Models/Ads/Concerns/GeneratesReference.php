<?php

declare(strict_types=1);

namespace App\Models\Ads\Concerns;

trait GeneratesReference
{
    protected static function bootGeneratesReference(): void
    {
        static::creating(function (self $model): void {
            $column = static::$referenceColumn ?? 'reference';

            if (empty($model->{$column})) {
                $model->{$column} = static::generateUniqueReference();
            }
        });
    }

    public static function generateUniqueReference(): string
    {
        $prefix = static::$referencePrefix ?? 'REF';
        $column = static::$referenceColumn ?? 'reference';

        do {
            $candidate = sprintf('%s-%06d', $prefix, random_int(1, 999999));
        } while (static::where($column, $candidate)->exists());

        return $candidate;
    }
}