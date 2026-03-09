<?php

namespace ArtOfWifi\StatamicIndexnow\Console\Commands;

use ArtOfWifi\StatamicIndexnow\Models\IndexNowSubmission;
use Illuminate\Console\Command;

class PruneSubmissionsCommand extends Command
{
    protected $signature = 'indexnow:prune {--days=90 : Delete submissions older than this many days}';

    protected $description = 'Delete old IndexNow submission records';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = IndexNowSubmission::query()
            ->where('submitted_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Deleted {$deleted} submission record(s) older than {$days} days.");

        return self::SUCCESS;
    }
}
