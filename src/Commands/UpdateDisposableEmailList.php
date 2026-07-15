<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Support\Email;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class UpdateDisposableEmailList extends Command
{
    protected $signature = 'erag:sync-disposable-email-list';

    protected $description = 'Fetch and update the disposable email domains list';

    public function handle(): int
    {
        Email::clearCache();

        $remoteUrls = $this->remoteUrls();
        $directory = config('disposable-email.blacklist_file');

        if (! is_string($directory) || trim($directory) === '') {
            $this->error('Invalid disposable-email.blacklist_file config value.');

            return self::FAILURE;
        }

        if ($remoteUrls === []) {
            $this->error('No valid remote URLs configured in disposable-email.remote_url.');

            return self::FAILURE;
        }

        if (! $this->ensureDirectoryExists($directory)) {
            return self::FAILURE;
        }

        $synced = 0;
        $failed = 0;

        foreach ($remoteUrls as $url) {
            $result = $this->syncUrl($url, $directory);

            if ($result) {
                $synced++;
            } else {
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Sync complete. Synced: {$synced}. Failed: {$failed}.");

        return $synced > 0 ? self::SUCCESS : self::FAILURE;
    }

    /**
     * @return array<int, string>
     */
    protected function remoteUrls(): array
    {
        $remoteUrls = config('disposable-email.remote_url', []);

        if (is_string($remoteUrls)) {
            $remoteUrls = [$remoteUrls];
        }

        if (! is_array($remoteUrls)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $url): string => is_string($url) ? trim($url) : '',
            $remoteUrls
        ), static fn (string $url): bool => filter_var($url, FILTER_VALIDATE_URL) !== false));
    }

    protected function ensureDirectoryExists(string $directory): bool
    {
        try {
            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
                $this->info("Directory created at: {$directory}");
            }

            return true;
        } catch (Throwable $exception) {
            $this->error("Unable to create blacklist directory [{$directory}]: {$exception->getMessage()}");

            return false;
        }
    }

    protected function syncUrl(string $url, string $directory): bool
    {
        $this->line("Fetching: {$url}");

        try {
            $response = Http::timeout($this->syncTimeout())->retry(2, 500)->get($url);
        } catch (Throwable $exception) {
            $this->error("Request failed for [{$url}]: {$exception->getMessage()}");

            return false;
        }

        if (! $response->successful()) {
            $this->error("Failed to fetch [{$url}]. HTTP status: {$response->status()}.");

            return false;
        }

        $domains = $this->normalizeToDomains($response->body());

        if ($domains === []) {
            $this->warn("No valid domains found in [{$url}]. Skipping write.");

            return false;
        }

        $filePath = $directory.DIRECTORY_SEPARATOR.$this->filenameForUrl($url);
        $contents = implode(PHP_EOL, $domains).PHP_EOL;

        try {
            File::put($filePath, $contents);
        } catch (Throwable $exception) {
            $this->error("Unable to write [{$filePath}]: {$exception->getMessage()}");

            return false;
        }

        $this->info('Saved '.number_format(count($domains))." domains to {$filePath}");

        return true;
    }

    protected function syncTimeout(): int
    {
        $timeout = config('disposable-email.sync_timeout', 30);

        if (! is_numeric($timeout)) {
            return 30;
        }

        $timeout = (int) $timeout;

        return $timeout > 0 ? $timeout : 30;
    }

    /**
     * @return array<int, string>
     */
    protected function normalizeToDomains(string $input): array
    {
        $domains = [];

        $jsonDecoded = json_decode($input, true);

        if (is_array($jsonDecoded)) {
            $domains = $this->extractDomainsFromArray($jsonDecoded);
        } else {
            $domains = preg_split('/\r\n|\r|\n/', $input) ?: [];
        }

        $domains = array_values(array_unique(array_filter(array_map(
            fn (string $domain): string => $this->normalizeDomain($domain),
            array_filter($domains, 'is_string')
        ))));

        sort($domains);

        return $domains;
    }

    /**
     * @return array<int, string>
     */
    protected function extractDomainsFromArray(array $items): array
    {
        $domains = [];

        foreach ($items as $item) {
            if (is_string($item)) {
                $domains[] = $item;
            } elseif (is_array($item)) {
                $domains = array_merge($domains, $this->extractDomainsFromArray($item));
            }
        }

        return $domains;
    }

    protected function normalizeDomain(string $domain): string
    {
        $domain = strtolower(trim($domain));

        if (str_contains($domain, '@')) {
            [, $domain] = explode('@', $domain, 2);
            $domain = trim($domain);
        }

        return preg_match('/^[a-z0-9.-]+\.[a-z]{2,}$/', $domain) ? $domain : '';
    }

    protected function filenameForUrl(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $name = is_string($path) ? pathinfo($path, PATHINFO_FILENAME) : '';
        $name = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name));
        $name = trim($name, '-_');

        return ($name === '' ? 'disposable-domains' : $name).'.txt';
    }
}
