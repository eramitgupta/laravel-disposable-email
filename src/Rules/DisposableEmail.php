<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Translation\PotentiallyTranslatedString;
use InvalidArgumentException;
use LaravelDisposableEmail\Contracts\Checker;

class DisposableEmail implements ValidationRule
{
    /**
     * @param  array<int, string>  $emailValidations
     */
    public function __construct(
        protected ?Checker $checker = null,
        protected array $emailValidations = []
    ) {
        $this->emailValidations = array_values(array_unique($emailValidations));
        $unsupportedValidations = array_diff($this->emailValidations, self::supportedEmailValidations());

        if ($unsupportedValidations !== []) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported email validation parameter(s): %s.',
                implode(', ', $unsupportedValidations)
            ));
        }
    }

    /**
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->emailValidations !== []) {
            $validator = Validator::make(
                [$attribute => $value],
                [$attribute => ['email:'.implode(',', $this->emailValidations)]]
            );

            if ($validator->fails()) {
                $fail($validator->errors()->first($attribute));

                return;
            }
        }

        if (! str_contains((string) $value, '@')) {
            $fail(__('The :attribute must contain an "@" symbol.'));

            return;
        }

        $result = $this->resolveChecker()->check((string) $value);

        if ($result->disposable()) {
            $fail(__('The :attribute belongs to an unauthorized email provider.'));
        }
    }

    /**
     * @return array<int, string>
     */
    public static function supportedEmailValidations(): array
    {
        return ['rfc', 'strict', 'dns', 'spoof', 'filter', 'filter_unicode'];
    }

    protected function resolveChecker(): Checker
    {
        return $this->checker ??= app(Checker::class);
    }
}
