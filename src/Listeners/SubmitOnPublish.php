<?php

namespace ArtOfWifi\StatamicIndexnow\Listeners;

use ArtOfWifi\StatamicIndexnow\IndexNowClient;
use Statamic\Events\EntrySaved;

class SubmitOnPublish
{
    public function handle(EntrySaved $event): void
    {
        if (! config('statamic-indexnow.auto_submit')) {
            return;
        }

        if (empty(config('statamic-indexnow.key'))) {
            return;
        }

        $entry = $event->entry;

        if (! $entry->published()) {
            return;
        }

        $excludeCollections = config('statamic-indexnow.exclude_collections', []);

        if (in_array($entry->collectionHandle(), $excludeCollections)) {
            return;
        }

        $uri = $entry->uri();

        if ($uri === null) {
            return;
        }

        $client = app(IndexNowClient::class);
        $url = $client->buildProductionUrl($uri);

        $client->submitSingle($entry->id(), $url);
    }
}
