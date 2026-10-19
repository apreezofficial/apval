<?php

declare(strict_types=1);

namespace App\Services\Ads;

use App\Models\Ads\AdConfig;
use Illuminate\Support\Facades\Cache;

class AdSettingService
{
    protected const string CACHE_KEY = 'ads:settings';

    protected const int CACHE_TTL_SECONDS = 3600;

    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->all();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default ?? config("ads.defaults.{$key}");
    }

    public function set(string $key, mixed $value, ?string $group = null): AdConfig
    {
        $config = AdConfig::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group]
        );

        $this->flush();

        return $config;
    }

    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL_SECONDS, function (): array {
            return AdConfig::query()->pluck('value', 'key')->toArray();
        });
    }

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}