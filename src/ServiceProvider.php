<?php

namespace ArtOfWifi\StatamicIndexnow;

use ArtOfWifi\StatamicIndexnow\Console\Commands\PruneSubmissionsCommand;
use ArtOfWifi\StatamicIndexnow\Http\Controllers\IndexNowUtilityController;
use ArtOfWifi\StatamicIndexnow\Listeners\SubmitOnPublish;
use Statamic\Events\EntrySaved;
use Statamic\Facades\Utility;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    /** @var array<class-string, class-string[]> */
    protected $listen = [
        EntrySaved::class => [
            SubmitOnPublish::class,
        ],
    ];

    /** @var list<class-string> */
    protected $commands = [
        PruneSubmissionsCommand::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->singleton(IndexNowClient::class);
    }

    public function bootAddon(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        Utility::extend(function () {
            Utility::register('index-now')
                ->title('IndexNow')
                ->icon('earth')
                ->description('Submit URLs to IndexNow for faster indexing by Bing, Yandex, and other search engines.')
                ->action([IndexNowUtilityController::class, 'index'])
                ->routes(function ($router) {
                    $router->post('submit', [IndexNowUtilityController::class, 'submit'])->name('submit');
                });
        });
    }
}
