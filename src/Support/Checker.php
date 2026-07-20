<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class Checker
{
    public function __construct(private readonly Domain $domains = new Domain) {}

    public function check(string $emailOrDomain): DisposableEmailResult
    {
        $domain = Domain::extract($emailOrDomain);

        if ($domain === '') {
            return new DisposableEmailResult(false, '');
        }

        $blockSubdomains = (bool) config('disposable-email.block_subdomains', true);
        $whitelist = Matcher::find($domain, $this->domains->whitelist(), $blockSubdomains);

        if ($whitelist !== null) {
            return new DisposableEmailResult(false, $domain, $whitelist, 'whitelist');
        }

        $sources = $this->domains->sources();
        $matched = Matcher::find($domain, $sources, $blockSubdomains);

        if ($matched === null) {
            return new DisposableEmailResult(false, $domain);
        }

        return new DisposableEmailResult(true, $domain, $matched, $sources[$matched] ?? 'built-in');
    }

    public function email(string $email): bool
    {
        return $this->check($email)->disposable();
    }

    public function domain(string $emailOrDomain): bool
    {
        return $this->check($emailOrDomain)->disposable();
    }
}
