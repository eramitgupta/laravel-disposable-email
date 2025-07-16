<?php

namespace EragLaravelDisposableEmail;

use EragLaravelDisposableEmail\Commands\InstallDisposableEmail;
use EragLaravelDisposableEmail\Commands\UpdateDisposableEmailList;
use EragLaravelDisposableEmail\Rules\DisposableEmailRule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class LaravelDisposableEmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/disposable-email.php', 'disposable-email'
        );

        $this->commands([
            InstallDisposableEmail::class,
            UpdateDisposableEmailList::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/disposable-email.php' => config_path('disposable-email.php'),
        ], 'erag:publish-disposable-config');

        $this->app->singleton('disposable-email', function ($app) {
            return new DisposableEmailRule;
        });
    }

    public function boot(): void
    {
        Validator::extend('disposable_email', function ($attribute, $value, $parameters, $validator) {
            $rule = new DisposableEmailRule;

            $failCallback = function ($message) use (&$error) {
                $error = $message;
            };

            $rule->validate($attribute, $value, $failCallback ?? fn () => null);

            return empty($error);
        }, __('The :attribute belongs to an unauthorized email provider.'));

        Blade::if('disposableEmail', function (string $email) {
            return DisposableEmailRule::isDisposable($email);
        });
    }
}
