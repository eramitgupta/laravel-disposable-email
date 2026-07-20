<?php

namespace EragLaravelDisposableEmail\Commands;

use EragLaravelDisposableEmail\Support\Stats as StatsData;
use Illuminate\Console\Command;

class Stats extends Command
{
    protected $signature = 'disposable:stats';

    protected $description = 'Show disposable email package domain and cache stats.';

    public function handle(): void
    {
        $this->table(['Metric', 'Value'], (new StatsData)->rows());
    }
}
