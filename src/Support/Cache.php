<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

use EragLaravelDisposableEmail\Enums\LaravelVersion;
use Illuminate\Support\Facades\Cache as LaravelCache;

class Cache
{
    public const PROVIDERS = 'erag-unauthorized-email-providers';

    public const SOURCES = 'erag-unauthorized-email-provider-sources';

    public const BUILT_IN = 'erag-disposable-email-built-in-domains';

    public static function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl ??= (int) config('disposable-email.cache_ttl', 60);

        $application = app();

        if (method_exists($application, 'version')
            && version_compare($application->version(), LaravelVersion::FLEXIBLE_CACHE->value, '>=')) {
            return LaravelCache::flexible($key, [$ttl / 2, $ttl * 2], $callback);
        }

        return LaravelCache::remember($key, $ttl, $callback);
    }

    public static function clear(): void
    {
        LaravelCache::forget(self::PROVIDERS);
        LaravelCache::forget(self::SOURCES);
        LaravelCache::forget(self::BUILT_IN);
    }
}
