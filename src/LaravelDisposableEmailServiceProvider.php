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
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge config file
        $this->mergeConfigFrom(
            __DIR__.'/../config/disposable-email.php', 'disposable-email'
        );

        // Register commands
        $this->commands([
            InstallDisposableEmail::class,
            UpdateDisposableEmailList::class,
        ]);

        // Publish config
        $this->publishes([
            __DIR__.'/../config/disposable-email.php' => config_path('disposable-email.php'),
        ], 'erag:publish-disposable-config');


        // Bind singleton to 'disposable-email'
        $this->app->singleton('disposable-email', function ($app) {
            return new DisposableEmailRule();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register custom validation rule
        Validator::extend('disposable_email', function ($attribute, $value, $parameters, $validator) {
            $rule = new DisposableEmailRule();

            $failCallback = function ($message) use (&$error) {
                $error = $message;
            };

            $rule->validate($attribute, $value, $failCallback ?? fn() => null);

            return empty($error);
        }, 'The :attribute belongs to an unauthorized email provider.');


        Blade::if('disposableEmail', function (string $email) {
            return DisposableEmailRule::isDisposable($email);
        });
    }
}
