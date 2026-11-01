<?php

namespace ArtOfWifi\StatamicIndexnow\Tests;

use ArtOfWifi\StatamicIndexnow\ServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Statamic\Extend\Manifest;
use Statamic\Providers\StatamicServiceProvider;
use Statamic\Statamic;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            StatamicServiceProvider::class,
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Statamic' => Statamic::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('statamic-indexnow.key', 'test-key-12345678');
        $app['config']->set('statamic-indexnow.production_url', 'https://example.com');
        $app['config']->set('statamic-indexnow.endpoint', 'https://api.indexnow.org/indexnow');
        $app['config']->set('statamic.users.repository', 'file');
        $app['config']->set('statamic.stache.stores.users', [
            'class' => \Statamic\Stache\Stores\UsersStore::class,
            'directory' => __DIR__ . '/__fixtures__/users',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Mock the addon manifest so Statamic recognizes our addon
        $this->app->instance(Manifest::class, new class extends Manifest
        {
            public function build(): void
            {
                $this->manifest = [
                    'artofwifi/statamic-indexnow' => [
                        'id' => 'artofwifi/statamic-indexnow',
                        'namespace' => 'ArtOfWifi\\StatamicIndexnow',
                    ],
                ];
            }

            public function manifest(): array
            {
                if (! $this->manifest) {
                    $this->build();
                }

                return $this->manifest;
            }
        });
    }
}
