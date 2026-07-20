<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Rules;

use Closure;
use EragLaravelDisposableEmail\Support\Checker;
use EragLaravelDisposableEmail\Support\DisposableEmailResult;
use EragLaravelDisposableEmail\Support\Domain;
use EragLaravelDisposableEmail\Support\Email;
use EragLaravelDisposableEmail\Support\Matcher;
use EragLaravelDisposableEmail\Support\Modes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;

class DisposableEmailRule implements ValidationRule
{
    /**
     * @var array<int, string>
     */
    protected array $unauthorizedEmail;

    /**
     * @var array<int, string>
     */
    protected array $modes;

    private bool $usesDefaultProviders;

    /**
     * @param  array<int, string>  $unauthorizedEmail
     * @param  array<int, string>  $modes
     */
    public function __construct(
        array $unauthorizedEmail = [],
        array $modes = [],
        private readonly Checker $checker = new Checker,
    ) {
        $this->unauthorizedEmail = $unauthorizedEmail;
        $this->usesDefaultProviders = $unauthorizedEmail === [];
        $this->modes = Modes::parse($modes);
    }

    /**
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $email = strtolower(trim((string) $value));

        if (! str_contains($email, '@')) {
            $fail(__('The :attribute must contain an "@" symbol.'));

            return;
        }

        if (! $this->passes($email, $this->modes)) {
            $fail(__('validation.email'));

            return;
        }

        if ($this->usesDefaultProviders) {
            if ($this->checker->email($email)) {
                $fail(__('The :attribute belongs to an unauthorized email provider.'));
            }

            return;
        }

        $domain = Domain::extract($email);
        $whitelist = (new Domain)->whitelist();

        if (Matcher::find($domain, $whitelist, (bool) config('disposable-email.block_subdomains', true)) !== null) {
            return;
        }

        $unauthorized = array_fill_keys($this->unauthorizedEmail, 'custom');

        if (Matcher::find($domain, $unauthorized, (bool) config('disposable-email.block_subdomains', true)) !== null) {
            $fail(__('The :attribute belongs to an unauthorized email provider.'));
        }
    }

    public static function email(string $email): bool
    {
        return (new Checker)->email($email);
    }

    public static function domain(string $emailOrDomain): bool
    {
        return (new Checker)->domain($emailOrDomain);
    }

    public static function check(string $emailOrDomain): DisposableEmailResult
    {
        return (new Checker)->check($emailOrDomain);
    }

    public static function rule(string ...$modes): self
    {
        return new self(modes: $modes);
    }

    public static function make(string ...$modes): self
    {
        return self::rule(...$modes);
    }

    /**
     * @return array<int, string>
     */
    public static function getDefaultUnauthorizedProviders(): array
    {
        return (new Domain)->domains();
    }

    /**
     * @return array<int, string>
     */
    public static function getBuiltInProviders(): array
    {
        return Email::domains();
    }

    /**
     * @param  array<int, string>  $modes
     */
    public function passes(string $email, array $modes): bool
    {
        if ($modes === []) {
            return true;
        }

        return Validator::make(
            ['email' => $email],
            ['email' => ['email:'.implode(',', $modes)]]
        )->passes();
    }
}
