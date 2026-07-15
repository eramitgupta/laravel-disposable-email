<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Services;

use LaravelDisposableEmail\Contracts\Matcher;

class DomainMatcher implements Matcher
{
    public function find(string $domain, array $domainMap): ?string
    {
        if (array_key_exists($domain, $domainMap)) {
            return $domain;
        }

        if (! config('disposable-email.block_subdomains', true)) {
            return null;
        }

        $parts = explode('.', $domain);

        while (count($parts) > 2) {
            array_shift($parts);
            $parentDomain = implode('.', $parts);

            if (array_key_exists($parentDomain, $domainMap)) {
                return $parentDomain;
            }
        }

        return null;
    }
}
