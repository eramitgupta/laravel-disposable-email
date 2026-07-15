<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Contracts;

interface Matcher
{
    /**
     * @param  array<string, string>  $domainMap
     */
    public function find(string $domain, array $domainMap): ?string;
}
