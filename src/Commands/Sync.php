<?php

declare(strict_types=1);

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Support\Cache;
use EragLaravelDisposableEmail\Support\ResponseParser;
use EragLaravelDisposableEmail\Support\UrlList;
use Illuminate\Console\Command;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

class Sync extends Command
{
    protected $signature = 'erag:sync-disposable-email-list';

    protected $description = 'Fetch and update the disposable email domains list';

    public function handle(): int
    {
        Cache::clear();

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
        return UrlList::from(config('disposable-email.remote_url', []));
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
            $response = $this->fetch($url);
        } catch (Throwable $exception) {
            $this->error("Request failed for [{$url}]: {$exception->getMessage()}");

            return false;
        }

        if (! $response->successful()) {
            $this->error("Failed to fetch [{$url}]. HTTP status: {$response->status()}.");

            return false;
        }

        $domains = ResponseParser::parse($response->body());

        if ($domains === []) {
            $this->warn("No valid domains found in [{$url}]. Skipping write.");

            return false;
        }

        $filePath = $directory.DIRECTORY_SEPARATOR.$this->filename($url);
        try {
            $this->write($filePath, $domains);
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

    private function filename(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $name = is_string($path) ? pathinfo($path, PATHINFO_FILENAME) : '';
        $name = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $name));
        $name = trim($name, '-_');

        return ($name === '' ? 'disposable-domains' : $name).'.txt';
    }

    private function fetch(string $url): Response
    {
        return Http::timeout($this->syncTimeout())->retry(2, 500)->get($url);
    }

    /**
     * @param  array<int, string>  $domains
     */
    private function write(string $path, array $domains): void
    {
        File::put($path, implode(PHP_EOL, $domains).PHP_EOL);
    }
}
