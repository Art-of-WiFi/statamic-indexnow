<?php

namespace ArtOfWifi\StatamicIndexnow\Tests\Feature;

use ArtOfWifi\StatamicIndexnow\IndexNowClient;
use ArtOfWifi\StatamicIndexnow\Listeners\SubmitOnPublish;
use ArtOfWifi\StatamicIndexnow\Tests\TestCase;
use Mockery;
use Statamic\Events\EntrySaved;

class SubmitOnPublishTest extends TestCase
{
    public function test_submits_when_auto_submit_enabled(): void
    {
        config(['statamic-indexnow.auto_submit' => true]);

        $entry = Mockery::mock();
        $entry->shouldReceive('published')->andReturn(true);
        $entry->shouldReceive('collectionHandle')->andReturn('pages');
        $entry->shouldReceive('uri')->andReturn('/test-page');
        $entry->shouldReceive('id')->andReturn('test-entry-id');

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldReceive('buildProductionUrl')
            ->with('/test-page')
            ->andReturn('https://example.com/test-page');
        $client->shouldReceive('submitSingle')
            ->with('test-entry-id', 'https://example.com/test-page')
            ->once();

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }

    public function test_skips_when_auto_submit_disabled(): void
    {
        config(['statamic-indexnow.auto_submit' => false]);

        $entry = Mockery::mock();
        $entry->shouldNotReceive('published');

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldNotReceive('submitSingle');

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }

    public function test_skips_when_key_not_configured(): void
    {
        config([
            'statamic-indexnow.auto_submit' => true,
            'statamic-indexnow.key' => null,
        ]);

        $entry = Mockery::mock();
        $entry->shouldNotReceive('published');

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldNotReceive('submitSingle');

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }

    public function test_skips_unpublished_entries(): void
    {
        config(['statamic-indexnow.auto_submit' => true]);

        $entry = Mockery::mock();
        $entry->shouldReceive('published')->andReturn(false);

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldNotReceive('submitSingle');

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }

    public function test_skips_excluded_collections(): void
    {
        config([
            'statamic-indexnow.auto_submit' => true,
            'statamic-indexnow.exclude_collections' => ['internal'],
        ]);

        $entry = Mockery::mock();
        $entry->shouldReceive('published')->andReturn(true);
        $entry->shouldReceive('collectionHandle')->andReturn('internal');

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldNotReceive('submitSingle');

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }

    public function test_skips_entries_without_uri(): void
    {
        config(['statamic-indexnow.auto_submit' => true]);

        $entry = Mockery::mock();
        $entry->shouldReceive('published')->andReturn(true);
        $entry->shouldReceive('collectionHandle')->andReturn('pages');
        $entry->shouldReceive('uri')->andReturn(null);

        $client = Mockery::mock(IndexNowClient::class);
        $client->shouldNotReceive('submitSingle');

        $this->app->instance(IndexNowClient::class, $client);

        $event = new EntrySaved($entry);
        $listener = new SubmitOnPublish;
        $listener->handle($event);
    }
}
