<?php

namespace EragLaravelDisposableEmail;

use EragLaravelDisposableEmail\Commands\Install;
use EragLaravelDisposableEmail\Commands\Stats;
use EragLaravelDisposableEmail\Commands\Sync;
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
            Stats::class,
            Install::class,
            Sync::class,
        ]);

        $this->publishes([
            __DIR__.'/../config/disposable-email.php' => config_path('disposable-email.php'),
        ], 'erag:publish-disposable-config');

        $this->app->singleton('disposable-email', function () {
            return new DisposableEmailRule;
        });
    }

    public function boot(): void
    {
        Validator::extend('disposable_email', function (string $attribute, mixed $value, array $parameters): bool {
            $passes = true;
            $rule = new DisposableEmailRule(modes: $parameters);
            $rule->validate($attribute, $value, function () use (&$passes): void {
                $passes = false;
            });

            return $passes;
        }, __('The :attribute belongs to an unauthorized email provider.'));

        Blade::if('disposableEmail', function (string $email) {
            return DisposableEmailRule::email($email);
        });
    }
}
