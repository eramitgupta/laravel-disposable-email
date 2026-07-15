<?php

declare(strict_types=1);

namespace LaravelDisposableEmail\Services;

use LaravelDisposableEmail\Contracts\Loader;
use LaravelDisposableEmail\Data\BuiltInDomains;
use LaravelDisposableEmail\Support\Cache;
use LaravelDisposableEmail\Support\Normalizer;
use Throwable;

class DomainLoader implements Loader
{
    public function all(): array
    {
        return array_keys($this->sourceMap());
    }

    public function sourceMap(): array
    {
        if (config('disposable-email.cache_enabled')) {
            return Cache::remember(
                'erag-unauthorized-email-provider-sources',
                fn (): array => $this->resolveSourceMap()
            );
        }

        return $this->resolveSourceMap();
    }

    public function builtIn(): array
    {
        return BuiltInDomains::all();
    }

    /**
     * @return array<string, string>
     */
    protected function resolveSourceMap(): array
    {
        $domains = array_fill_keys($this->builtIn(), 'built-in');
        $directory = config('disposable-email.blacklist_file');
        $files = is_string($directory) ? glob($directory.'/*.txt') ?: [] : [];

        foreach ($files as $file) {
            $content = $this->read($file);

            if ($content === null) {
                continue;
            }

            foreach (explode("\n", $content) as $line) {
                $domain = Normalizer::domain($line);

                if (Normalizer::isValid($domain)) {
                    $domains[$domain] = 'custom';
                }
            }
        }

        return $domains;
    }

    private function read(string $file): ?string
    {
        if (! is_readable($file)) {
            return null;
        }

        try {
            $content = file_get_contents($file);
        } catch (Throwable) {
            return null;
        }

        return $content === false ? null : $content;
    }
}
