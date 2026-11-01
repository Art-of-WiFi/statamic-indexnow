<?php

namespace ArtOfWifi\StatamicIndexnow\Tests;

use ArtOfWifi\StatamicIndexnow\ServiceProvider;
use Illuminate\Support\Facades\File;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deleteSubmissionStore();
    }

    protected function tearDown(): void
    {
        $this->deleteSubmissionStore();

        parent::tearDown();
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('statamic-indexnow.key', 'test-key-12345678');
        $app['config']->set('statamic-indexnow.production_url', 'https://example.com');
        $app['config']->set('statamic-indexnow.endpoint', 'https://api.indexnow.org/indexnow');
    }

    protected function deleteSubmissionStore(): void
    {
        File::deleteDirectory(storage_path('statamic-indexnow'));
    }
}
