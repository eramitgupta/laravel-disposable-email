<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class ResponseParser
{
    /**
     * @return array<int, string>
     */
    public static function parse(string $input): array
    {
        $decoded = json_decode($input, true);
        $values = is_array($decoded) ? self::flatten($decoded) : (preg_split('/\r\n|\r|\n/', $input) ?: []);
        $domains = [];

        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }

            $domain = Domain::normalize($value);

            if ($domain !== '') {
                $domains[$domain] = true;
            }
        }

        $domains = array_keys($domains);
        sort($domains);

        return $domains;
    }

    /**
     * @param  array<array-key, mixed>  $items
     * @return array<int, string>
     */
    private static function flatten(array $items): array
    {
        $values = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $values[] = $item;
            } elseif (is_array($item)) {
                array_push($values, ...self::flatten($item));
            }
        }

        return $values;
    }
}
