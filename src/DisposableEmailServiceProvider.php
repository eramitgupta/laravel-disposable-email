<?php

declare(strict_types=1);

namespace LaravelDisposableEmail;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use LaravelDisposableEmail\Commands\Install;
use LaravelDisposableEmail\Commands\Stats;
use LaravelDisposableEmail\Commands\Sync;
use LaravelDisposableEmail\Contracts\Checker;
use LaravelDisposableEmail\Contracts\Loader;
use LaravelDisposableEmail\Contracts\Matcher;
use LaravelDisposableEmail\Rules\DisposableEmail;
use LaravelDisposableEmail\Services\DomainChecker;
use LaravelDisposableEmail\Services\DomainLoader;
use LaravelDisposableEmail\Services\DomainMatcher;

class DisposableEmailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/disposable-email.php', 'disposable-email'
        );

        $this->app->bind(Loader::class, DomainLoader::class);
        $this->app->bind(Matcher::class, DomainMatcher::class);
        $this->app->singleton(Checker::class, DomainChecker::class);
        $this->app->singleton('disposable-email', fn ($app): Checker => $app->make(Checker::class));

        $this->commands([Stats::class, Install::class, Sync::class]);

        $this->publishes([
            __DIR__.'/../config/disposable-email.php' => config_path('disposable-email.php'),
        ], 'erag:publish-disposable-config');
    }

    public function boot(): void
    {
        Validator::extend('disposable_email', function ($attribute, $value, $parameters) {
            $rule = new DisposableEmail(emailValidations: $parameters);
            $error = null;

            $rule->validate($attribute, $value, function ($message) use (&$error): void {
                $error = $message;
            });

            return $error === null;
        }, __('The :attribute belongs to an unauthorized email provider.'));

        Blade::if('disposableEmail', function (string $email): bool {
            return app(Checker::class)->email($email);
        });
    }
}
