<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Services;

use LaravelDisposableEmail\Contracts\Checker;
use LaravelDisposableEmail\Contracts\Loader;
use LaravelDisposableEmail\Contracts\Matcher;
use LaravelDisposableEmail\Support\Normalizer;
use LaravelDisposableEmail\Support\Result;
use LaravelDisposableEmail\Support\Whitelist;

class DomainChecker implements Checker
{
    public function __construct(
        protected Loader $loader,
        protected Matcher $matcher
    ) {}

    public function check(string $emailOrDomain): Result
    {
        $domain = Normalizer::domain($emailOrDomain);

        if ($domain === '') {
            return new Result(false, '');
        }

        $whitelistMatch = $this->matcher->find($domain, Whitelist::resolve());

        if ($whitelistMatch !== null) {
            return new Result(false, $domain, $whitelistMatch, 'whitelist');
        }

        $sourceMap = $this->loader->sourceMap();
        $matchedDomain = $this->matcher->find($domain, $sourceMap);

        if ($matchedDomain === null) {
            return new Result(false, $domain);
        }

        return new Result(true, $domain, $matchedDomain, $sourceMap[$matchedDomain] ?? 'built-in');
    }

    public function email(string $email): bool
    {
        return $this->check($email)->disposable();
    }

    public function domain(string $domain): bool
    {
        return $this->check($domain)->disposable();
    }
}
