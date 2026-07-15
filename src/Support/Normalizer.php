<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Support;

class Normalizer
{
    public static function domain(string $input): string
    {
        $input = strtolower(trim($input));

        if ($input === '') {
            return '';
        }

        if (str_contains($input, '@')) {
            [, $input] = explode('@', $input, 2);
        }

        return trim($input);
    }

    public static function isValid(string $domain): bool
    {
        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', self::domain($domain)) === 1;
    }
}
