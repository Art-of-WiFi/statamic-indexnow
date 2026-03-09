<?php

namespace ArtOfWifi\StatamicIndexnow\Tests\Unit;

use ArtOfWifi\StatamicIndexnow\IndexNowClient;
use ArtOfWifi\StatamicIndexnow\SubmissionStore;
use ArtOfWifi\StatamicIndexnow\Tests\TestCase;
use Illuminate\Support\Facades\Http;

class IndexNowClientTest extends TestCase
{
    public function test_submit_sends_urls_to_endpoint(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $client = app(IndexNowClient::class);

        $result = $client->submit([
            ['entry_id' => 'entry-1', 'url' => 'https://example.com/page-one'],
            ['entry_id' => 'entry-2', 'url' => 'https://example.com/page-two'],
        ]);

        $this->assertSame(2, $result['submitted']);
        $this->assertSame(0, $result['failed']);
        $this->assertEmpty($result['errors']);

        Http::assertSentCount(1);
    }

    public function test_submit_records_submissions_in_store(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $client = app(IndexNowClient::class);

        $client->submit([
            ['entry_id' => 'entry-1', 'url' => 'https://example.com/page-one'],
        ]);

        $store = app(SubmissionStore::class);
        $all = $store->all();

        $this->assertCount(1, $all);
        $this->assertSame('entry-1', $all->first()['entry_id']);
        $this->assertSame(200, $all->first()['status_code']);
    }

    public function test_submit_handles_api_failure(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('Rate limited', 429),
        ]);

        $client = app(IndexNowClient::class);

        $result = $client->submit([
            ['entry_id' => 'entry-1', 'url' => 'https://example.com/page-one'],
        ]);

        $this->assertSame(0, $result['submitted']);
        $this->assertSame(1, $result['failed']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('429', $result['errors'][0]);
    }

    public function test_submit_chunks_large_batches(): void
    {
        config(['statamic-indexnow.batch_size' => 2]);

        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $client = app(IndexNowClient::class);

        $result = $client->submit([
            ['entry_id' => 'entry-1', 'url' => 'https://example.com/page-1'],
            ['entry_id' => 'entry-2', 'url' => 'https://example.com/page-2'],
            ['entry_id' => 'entry-3', 'url' => 'https://example.com/page-3'],
        ]);

        $this->assertSame(3, $result['submitted']);
        Http::assertSentCount(2);
    }

    public function test_submit_single_sends_one_url(): void
    {
        Http::fake([
            'api.indexnow.org/*' => Http::response('', 200),
        ]);

        $client = app(IndexNowClient::class);

        $result = $client->submitSingle('entry-1', 'https://example.com/page-one');

        $this->assertSame(1, $result['submitted']);

        $store = app(SubmissionStore::class);
        $this->assertCount(1, $store->all());
    }

    public function test_build_production_url_with_uri(): void
    {
        $client = app(IndexNowClient::class);

        $this->assertSame('https://example.com/my-page', $client->buildProductionUrl('/my-page'));
    }

    public function test_build_production_url_with_root_uri(): void
    {
        $client = app(IndexNowClient::class);

        $this->assertSame('https://example.com', $client->buildProductionUrl('/'));
    }

    public function test_build_production_url_with_empty_uri(): void
    {
        $client = app(IndexNowClient::class);

        $this->assertSame('https://example.com', $client->buildProductionUrl(''));
    }

    public function test_build_production_url_strips_trailing_slash(): void
    {
        config(['statamic-indexnow.production_url' => 'https://example.com/']);

        $client = app(IndexNowClient::class);

        $this->assertSame('https://example.com/my-page', $client->buildProductionUrl('/my-page'));
    }
}
