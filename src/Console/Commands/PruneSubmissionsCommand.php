<?php

namespace ArtOfWifi\StatamicIndexnow\Console\Commands;

use ArtOfWifi\StatamicIndexnow\SubmissionStore;
use Illuminate\Console\Command;

class PruneSubmissionsCommand extends Command
{
    protected $signature = 'indexnow:prune {--days=90 : Delete submissions older than this many days}';

    protected $description = 'Delete old IndexNow submission records';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $store = app(SubmissionStore::class);

        $deleted = $store->prune($days);

        $this->info("Deleted {$deleted} submission record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
