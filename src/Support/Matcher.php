<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class Matcher
{
    /**
     * @param  array<string, string>  $domainMap
     */
    public static function find(string $domain, array $domainMap, bool $blockSubdomains = true): ?string
    {
        if (array_key_exists($domain, $domainMap)) {
            return $domain;
        }

        if (! $blockSubdomains) {
            return null;
        }

        $parts = explode('.', $domain);

        while (count($parts) > 2) {
            array_shift($parts);
            $parent = implode('.', $parts);

            if (array_key_exists($parent, $domainMap)) {
                return $parent;
            }
        }

        return null;
    }
}
