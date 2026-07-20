<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Support;

class SourceMap
{
    /**
     * @return array<string, string>
     */
    public function all(): array
    {
        return array_merge(array_fill_keys(Email::domains(), 'built-in'), $this->custom());
    }

    /**
     * @return array<string, string>
     */
    public function custom(): array
    {
        $directory = config('disposable-email.blacklist_file');

        if (! is_string($directory) || trim($directory) === '') {
            return [];
        }

        $domains = [];

        foreach (glob($directory.'/*.txt') ?: [] as $file) {
            $content = file_get_contents($file);

            if ($content === false) {
                continue;
            }

            foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
                $domain = Domain::normalize($line);

                if ($domain !== '') {
                    $domains[$domain] = 'custom';
                }
            }
        }

        return $domains;
    }

    /**
     * @return array<string, string>
     */
    public function whitelist(): array
    {
        $configured = config('disposable-email.whitelist', []);

        if (! is_array($configured)) {
            return [];
        }

        $domains = [];

        foreach ($configured as $value) {
            if (! is_string($value)) {
                continue;
            }

            $domain = Domain::normalize($value);

            if ($domain !== '') {
                $domains[$domain] = 'whitelist';
            }
        }

        return $domains;
    }
}
