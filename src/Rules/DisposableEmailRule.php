<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Rules;

use Closure;
use EragLaravelDisposableEmail\Support\DisposableEmailResult;
use EragLaravelDisposableEmail\Support\Email;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class DisposableEmailRule implements ValidationRule
{
    /**
     * List of unauthorized email providers.
     *
     * @var array<int, string>
     */
    protected array $unauthorizedEmail;

    /**
     * Create a new rule instance.
     *
     * @param  array<int, string>  $unauthorizedEmail
     */
    public function __construct(
        array $unauthorizedEmail = []
    ) {
        $this->unauthorizedEmail = empty($unauthorizedEmail) ? self::getDefaultUnauthorizedProviders() : $unauthorizedEmail;
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $value = strtolower(trim($value));

        if (! str_contains($value, '@')) {
            $fail(__('The :attribute must contain an "@" symbol.'));

            return;
        }

        $emailProvider = self::extractDomain($value);

        if (self::findDomainMatch($emailProvider, self::whitelistMap()) !== null) {
            return;
        }

        $unauthorizedDomains = array_fill_keys($this->unauthorizedEmail, 'custom');

        if (self::findDomainMatch($emailProvider, $unauthorizedDomains) !== null) {
            $fail(__('The :attribute belongs to an unauthorized email provider.'));
        }
    }

    /**
     * @deprecated Use Disposable::Email($email) or DisposableEmailRule::email($email).
     */
    public static function isDisposable(string $email): bool
    {
        return self::email($email);
    }

    public static function email(string $email): bool
    {
        return self::check($email)->disposable();
    }

    public static function domain(string $emailOrDomain): bool
    {
        return self::check($emailOrDomain)->disposable();
    }

    public static function check(string $emailOrDomain): DisposableEmailResult
    {
        $emailProvider = self::extractDomain($emailOrDomain);

        if ($emailProvider === '') {
            return new DisposableEmailResult(false, '');
        }

        $whitelistMatch = self::findDomainMatch($emailProvider, self::whitelistMap());

        if ($whitelistMatch !== null) {
            return new DisposableEmailResult(false, $emailProvider, $whitelistMatch, 'whitelist');
        }

        $sourceMap = self::disposableProviderSourceMap();
        $matchedDomain = self::findDomainMatch($emailProvider, $sourceMap);

        if ($matchedDomain === null) {
            return new DisposableEmailResult(false, $emailProvider);
        }

        return new DisposableEmailResult(true, $emailProvider, $matchedDomain, $sourceMap[$matchedDomain] ?? 'built-in');
    }

    public static function rule(): self
    {
        return new self;
    }

    public static function make(): self
    {
        return self::rule();
    }

    public static function getDefaultUnauthorizedProviders(): array
    {
        $cacheEnabled = config('disposable-email.cache_enabled');

        if ($cacheEnabled) {
            return Email::cache(
                'erag-unauthorized-email-providers',
                fn () => self::getUnauthorizedProviders()
            );
        }

        return self::getUnauthorizedProviders();
    }

    public static function getBuiltInProviders(): array
    {
        return Email::domains();
    }

    protected static function getUnauthorizedProviders(): array
    {
        return array_keys(self::getUnauthorizedProviderSourceMap());
    }

    protected static function getUnauthorizedProviderSourceMap(): array
    {
        $directory = config('disposable-email.blacklist_file');
        $files = glob($directory.'/*.txt') ?: [];

        $allDomains = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                continue;
            }

            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                $line = strtolower(trim($line));
                if (empty($line)) {
                    continue;
                }

                if (str_contains($line, '@')) {
                    [, $domain] = explode('@', $line, 2);
                    $line = trim($domain);
                }

                if (preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $line)) {
                    $allDomains[$line] = 'custom';
                }
            }
        }

        $domains = Email::domains();
        $builtInDomains = array_fill_keys($domains, 'built-in');

        return array_merge($builtInDomains, $allDomains);
    }

    protected static function disposableProviderMap(): array
    {
        static $flippedProviders = null;

        if ($flippedProviders === null) {
            $flippedProviders = array_flip(self::getDefaultUnauthorizedProviders());
        }

        return $flippedProviders;
    }

    protected static function disposableProviderSourceMap(): array
    {
        static $providerSourceMap = null;

        if ($providerSourceMap === null) {
            $cacheEnabled = config('disposable-email.cache_enabled');

            if ($cacheEnabled) {
                $providerSourceMap = Email::cache(
                    'erag-unauthorized-email-provider-sources',
                    fn () => self::getUnauthorizedProviderSourceMap()
                );
            } else {
                $providerSourceMap = self::getUnauthorizedProviderSourceMap();
            }
        }

        return $providerSourceMap;
    }

    protected static function whitelistMap(): array
    {
        $whitelist = config('disposable-email.whitelist', []);
        $domains = [];

        if (! is_array($whitelist)) {
            return [];
        }

        foreach ($whitelist as $domain) {
            if (! is_string($domain)) {
                continue;
            }

            $domain = self::extractDomain($domain);

            if ($domain !== '' && preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain)) {
                $domains[$domain] = 'whitelist';
            }
        }

        return $domains;
    }

    protected static function findDomainMatch(string $domain, array $domainMap): ?string
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

    protected static function extractDomain(string $emailOrDomain): string
    {
        $emailOrDomain = strtolower(trim($emailOrDomain));

        if ($emailOrDomain === '') {
            return '';
        }

        if (str_contains($emailOrDomain, '@')) {
            [, $emailOrDomain] = explode('@', $emailOrDomain, 2);
            $emailOrDomain = trim($emailOrDomain);
        }

        return $emailOrDomain;
    }
}
