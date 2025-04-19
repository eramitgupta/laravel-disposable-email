<?php
declare(strict_types=1);

namespace EragLaravelDisposableEmail\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\File;
use Illuminate\Translation\PotentiallyTranslatedString;

final readonly class DisposableEmailRule implements ValidationRule
{
    /**
     * List of unauthorized email providers.
     *
     * @var array<int, string>
     */
    private array $unauthorizedEmail;

    /**
     * Create a new rule instance.
     *
     * @param array<int, string> $unauthorizedEmail
     */
    public function __construct(
        array $unauthorizedEmail = []
    )
    {
        $this->unauthorizedEmail = empty($unauthorizedEmail) ? self::getDefaultUnauthorizedProviders() : $unauthorizedEmail;
    }

    /**
     * Run the validation rule.
     *
     * @param Closure(string): PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (string) $value;
        $value = trim($value);


        if (!str_contains($value, '@')) {
            $fail(__('The :attribute must contain an "@" symbol.'));

            return;
        }

        [, $emailProvider] = explode('@', $value, 2);

        if (in_array($emailProvider, $this->unauthorizedEmail, true)) {
            $fail(__('The :attribute belongs to an unauthorized email provider.'));
        }
    }

    public static function isDisposable(string $email): bool
    {
        $email = trim($email);

        if (! str_contains($email, '@')) {
            return false;
        }

        [, $emailProvider] = explode('@', $email, 2);

        return in_array($emailProvider, self::getDefaultUnauthorizedProviders(), true);
    }


    private static function getDefaultUnauthorizedProviders(): array
    {
        $directory = dirname(config('disposable-email.blacklist_file'));

        $files = collect(glob($directory . '/*.txt'));

        $allDomains = $files->flatMap(function ($file) {
            return collect(explode("\n", File::get($file)))
                ->map(fn($line) => strtolower(trim($line)))
                ->map(function ($line) {
                    if (str_contains($line, '@')) {
                        [, $domain] = explode('@', $line, 2);
                        return trim($domain);
                    }
                    return $line;
                })
                ->filter(function ($line) {
                    return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $line);
                });
        })->unique()->values()->all();

        $domainArray= [
            '0-mail.com',
            '027168.com',
            '0815.ru',
        ];

        return array_values(array_unique([...$domainArray, ...$allDomains]));
    }

}
