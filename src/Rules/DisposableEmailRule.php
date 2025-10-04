<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Rules;

use Closure;
use EragLaravelDisposableEmail\Services\EmailServices;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

final class DisposableEmailRule implements ValidationRule
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

        [, $emailProvider] = explode('@', $value, 2);

        $flippedUnauthorized = array_flip($this->unauthorizedEmail);

        if (! empty($flippedUnauthorized[$emailProvider] ?? null)) {
            $fail(__('The :attribute belongs to an unauthorized email provider.'));
        }
    }

    public static function isDisposable(string $email): bool
    {
        $email = strtolower(trim($email));

        if (! str_contains($email, '@')) {
            return false;
        }

        [, $emailProvider] = explode('@', $email, 2);

        static $flippedProviders = null;
        if ($flippedProviders === null) {
            $flippedProviders = array_flip(self::getDefaultUnauthorizedProviders());
        }

        return array_key_exists($emailProvider, $flippedProviders);
    }

    public static function getDefaultUnauthorizedProviders(): array
    {
        $cacheEnabled = config('disposable-email.cache_enabled');

        if ($cacheEnabled) {
            return EmailServices::cache(
                'erag-unauthorized-email-providers',
                fn () => self::getUnauthorizedProviders()
            );
        }

        return self::getUnauthorizedProviders();
    }

    protected static function getUnauthorizedProviders(): array
    {
        $directory = config('disposable-email.blacklist_file');
        $files = glob($directory.'/*.txt');

        $allDomains = [];
        foreach ($files as $file) {
            $content = file_get_contents($file);
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
                    $allDomains[$line] = true;
                }
            }
        }

        $domains = EmailServices::domains();

        return array_keys(array_merge($allDomains, array_flip($domains)));
    }
}
