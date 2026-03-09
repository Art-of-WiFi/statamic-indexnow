<?php

namespace ArtOfWifi\StatamicIndexnow\Tests\Feature;

use ArtOfWifi\StatamicIndexnow\Models\IndexNowSubmission;
use ArtOfWifi\StatamicIndexnow\Tests\TestCase;

class PruneSubmissionsTest extends TestCase
{
    public function test_prune_deletes_old_records(): void
    {
        IndexNowSubmission::create([
            'entry_id' => 'old-entry',
            'url' => 'https://example.com/old',
            'batch_id' => 'batch-1',
            'status_code' => 200,
            'submitted_at' => now()->subDays(100),
        ]);

        IndexNowSubmission::create([
            'entry_id' => 'recent-entry',
            'url' => 'https://example.com/recent',
            'batch_id' => 'batch-2',
            'status_code' => 200,
            'submitted_at' => now()->subDays(10),
        ]);

        $this->artisan('indexnow:prune')
            ->expectsOutputToContain('1 submission record(s)')
            ->assertExitCode(0);

        $this->assertDatabaseCount('indexnow_submissions', 1);
        $this->assertDatabaseHas('indexnow_submissions', ['entry_id' => 'recent-entry']);
    }

    public function test_prune_with_custom_days(): void
    {
        IndexNowSubmission::create([
            'entry_id' => 'entry-1',
            'url' => 'https://example.com/page',
            'batch_id' => 'batch-1',
            'status_code' => 200,
            'submitted_at' => now()->subDays(40),
        ]);

        $this->artisan('indexnow:prune', ['--days' => 30])
            ->expectsOutputToContain('1 submission record(s)')
            ->assertExitCode(0);

        $this->assertDatabaseCount('indexnow_submissions', 0);
    }

    public function test_prune_with_no_old_records(): void
    {
        IndexNowSubmission::create([
            'entry_id' => 'entry-1',
            'url' => 'https://example.com/page',
            'batch_id' => 'batch-1',
            'status_code' => 200,
            'submitted_at' => now(),
        ]);

        $this->artisan('indexnow:prune')
            ->expectsOutputToContain('0 submission record(s)')
            ->assertExitCode(0);

        $this->assertDatabaseCount('indexnow_submissions', 1);
    }
}
