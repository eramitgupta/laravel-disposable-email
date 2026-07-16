<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class UrlList
{
    /**
     * @return array<int, string>
     */
    public static function from(mixed $value): array
    {
        if (is_string($value)) {
            $value = [$value];
        }

        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn (mixed $url): string => is_string($url) ? trim($url) : '',
            $value
        ), static fn (string $url): bool => filter_var($url, FILTER_VALIDATE_URL) !== false)));
    }
}
