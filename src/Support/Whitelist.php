<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Support;

class Whitelist
{
    /**
     * @return array<string, string>
     */
    public static function resolve(): array
    {
        $whitelist = config('disposable-email.whitelist', []);
        $domains = [];

        if (! is_array($whitelist)) {
            return [];
        }

        foreach ($whitelist as $domain) {
            if (is_string($domain)) {
                $domain = Normalizer::domain($domain);

                if ($domain !== '' && Normalizer::isValid($domain)) {
                    $domains[$domain] = 'whitelist';
                }
            }
        }

        return $domains;
    }
}
