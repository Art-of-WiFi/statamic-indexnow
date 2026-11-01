<?php

namespace ArtOfWifi\StatamicIndexnow\Tests\Feature;

use ArtOfWifi\StatamicIndexnow\SubmissionStore;
use ArtOfWifi\StatamicIndexnow\Tests\TestCase;
use Illuminate\Support\Facades\File;

class PruneSubmissionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $store = app(SubmissionStore::class);

        // Seed with test data via reflection to write directly
        $path = storage_path('statamic-indexnow/submissions.json');
        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode([
            [
                'entry_id' => 'old-entry',
                'url' => 'https://example.com/old',
                'batch_id' => 'batch-1',
                'status_code' => 200,
                'submitted_at' => now()->subDays(100)->toIso8601String(),
            ],
            [
                'entry_id' => 'recent-entry',
                'url' => 'https://example.com/recent',
                'batch_id' => 'batch-2',
                'status_code' => 200,
                'submitted_at' => now()->subDays(10)->toIso8601String(),
            ],
        ]));
    }

    protected function tearDown(): void
    {
        $path = storage_path('statamic-indexnow');

        if (File::isDirectory($path)) {
            File::deleteDirectory($path);
        }

        parent::tearDown();
    }

    public function test_prune_deletes_old_records(): void
    {
        $this->artisan('indexnow:prune')
            ->expectsOutputToContain('1 submission record(s)')
            ->assertExitCode(0);

        $store = app(SubmissionStore::class);
        $all = $store->all();

        $this->assertCount(1, $all);
        $this->assertSame('recent-entry', $all->first()['entry_id']);
    }

    public function test_prune_with_custom_days(): void
    {
        $this->artisan('indexnow:prune', ['--days' => 5])
            ->expectsOutputToContain('2 submission record(s)')
            ->assertExitCode(0);

        $store = app(SubmissionStore::class);
        $this->assertCount(0, $store->all());
    }

    public function test_prune_with_no_old_records(): void
    {
        // Replace with only recent data
        $path = storage_path('statamic-indexnow/submissions.json');
        File::put($path, json_encode([
            [
                'entry_id' => 'recent-entry',
                'url' => 'https://example.com/recent',
                'batch_id' => 'batch-1',
                'status_code' => 200,
                'submitted_at' => now()->toIso8601String(),
            ],
        ]));

        $this->artisan('indexnow:prune')
            ->expectsOutputToContain('0 submission record(s)')
            ->assertExitCode(0);

        $store = app(SubmissionStore::class);
        $this->assertCount(1, $store->all());
    }
}
