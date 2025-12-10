<?php

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Services\EmailServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

class UpdateDisposableEmailList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erag:sync-disposable-email-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update the disposable email domains list';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        EmailServices::clearCache();

        $remoteUrls = config('disposable-email.remote_url');
        $directory = config('disposable-email.blacklist_file');

        // Ensure directory exists
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
            $this->info("Directory created at: $directory");
        }

        foreach ($remoteUrls as $url) {

            $name = explode('.', basename($url))[0];

            $response = Http::get($url);

            if ($response->successful()) {
                // Extract original file name from URL
                $filePath = $directory.DIRECTORY_SEPARATOR.$name.'.txt';

                $raw = $response->body();
                $cleaned = $this->normalizeToTextList($raw);

                File::put($filePath, $cleaned);

                $this->info("Saved: $filePath from $url");
            } else {
                $this->error("Failed to fetch list from: $url");
            }
        }
    }

    protected function normalizeToTextList(string $input): string
    {
        $domains = [];

        $jsonDecoded = json_decode($input, true);

        if (is_array($jsonDecoded)) {
            foreach ($jsonDecoded as $item) {
                if (is_string($item)) {
                    $domains[] = trim($item);
                }
            }
        } else {
            // If not JSON, treat as plain text
            $lines = preg_split('/\r\n|\r|\n/', $input);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '' && preg_match('/^[a-z0-9\-\.]+\.[a-z]{2,}$/i', $line)) {
                    $domains[] = $line;
                }
            }
        }

        // Remove duplicates, sort, and return as string
        $unique = array_unique($domains);
        sort($unique);

        return implode(PHP_EOL, $unique);
    }
}
