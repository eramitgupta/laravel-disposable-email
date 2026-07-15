<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Support;

use Illuminate\Support\Facades\Cache as LaravelCache;
use LaravelDisposableEmail\Enums\LaravelVersion;

class Cache
{
    public static function remember(string $key, callable $callback): mixed
    {
        $ttl = config('disposable-email.cache_ttl');

        if (version_compare(app()->version(), LaravelVersion::FLEXIBLE_CACHE->value, '>=')) {
            return LaravelCache::flexible($key, [$ttl / 2, $ttl * 2], $callback);
        }

        return LaravelCache::remember($key, $ttl, $callback);
    }

    public static function flush(): void
    {
        LaravelCache::forget('erag-unauthorized-email-providers');
        LaravelCache::forget('erag-unauthorized-email-provider-sources');
    }
}
