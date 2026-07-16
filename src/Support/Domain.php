<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class Domain
{
    public function __construct(private readonly SourceMap $sources = new SourceMap) {}

    public static function extract(string $emailOrDomain): string
    {
        $value = strtolower(trim($emailOrDomain));

        if ($value === '') {
            return '';
        }

        if (str_contains($value, '@')) {
            [, $value] = explode('@', $value, 2);
        }

        return trim($value);
    }

    public static function normalize(string $emailOrDomain): string
    {
        $domain = self::extract($emailOrDomain);

        return self::isValid($domain) ? $domain : '';
    }

    public static function isValid(string $domain): bool
    {
        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain) === 1;
    }

    /**
     * @return array<string, string>
     */
    public function sources(): array
    {
        if (config('disposable-email.cache_enabled', false)) {
            return Cache::remember(Cache::SOURCES, fn (): array => $this->sources->all());
        }

        return $this->sources->all();
    }

    /**
     * @return array<int, string>
     */
    public function domains(): array
    {
        if (config('disposable-email.cache_enabled', false)) {
            return Cache::remember(Cache::PROVIDERS, fn (): array => array_keys($this->sources->all()));
        }

        return array_keys($this->sources->all());
    }

    /**
     * @return array<string, string>
     */
    public function whitelist(): array
    {
        return $this->sources->whitelist();
    }

    /**
     * @return array<string, string>
     */
    public function custom(): array
    {
        return $this->sources->custom();
    }
}
