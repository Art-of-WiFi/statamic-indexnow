<?php

namespace ArtOfWifi\StatamicIndexnow\Http\Controllers;

use ArtOfWifi\StatamicIndexnow\IndexNowClient;
use ArtOfWifi\StatamicIndexnow\Models\IndexNowSubmission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Statamic\Facades\Entry;
use Statamic\Http\Controllers\CP\CpController;

class IndexNowUtilityController extends CpController
{
    public function index(Request $request): View
    {
        $productionUrl = config('statamic-indexnow.production_url');
        $key = config('statamic-indexnow.key');
        $excludeCollections = config('statamic-indexnow.exclude_collections', []);
        $client = app(IndexNowClient::class);

        $lastSubmissions = IndexNowSubmission::query()
            ->selectRaw('entry_id, MAX(submitted_at) as last_submitted_at')
            ->groupBy('entry_id')
            ->pluck('last_submitted_at', 'entry_id');

        $query = Entry::query()->where('published', true);

        $entries = $query->get()
            ->reject(fn ($entry) => in_array($entry->collectionHandle(), $excludeCollections))
            ->map(function ($entry) use ($client, $lastSubmissions) {
                $lastSubmitted = $lastSubmissions->get($entry->id());
                $lastModified = $entry->lastModified();
                $uri = $entry->uri();

                if ($lastSubmitted === null) {
                    $status = 'never';
                } elseif ($lastModified && $lastModified->isAfter($lastSubmitted)) {
                    $status = 'modified';
                } else {
                    $status = 'submitted';
                }

                return [
                    'id' => $entry->id(),
                    'title' => $entry->get('title'),
                    'collection' => $entry->collectionHandle(),
                    'url' => $uri ? $client->buildProductionUrl($uri) : config('statamic-indexnow.production_url'),
                    'edit_url' => $entry->editUrl(),
                    'updated_at' => $lastModified?->format('Y-m-d H:i'),
                    'last_submitted' => $lastSubmitted
                        ? \Carbon\Carbon::parse($lastSubmitted)->format('Y-m-d H:i')
                        : null,
                    'status' => $status,
                ];
            })
            ->sortByDesc('updated_at')
            ->values();

        $collections = $entries->pluck('collection')->unique()->sort()->values();

        $collectionFilter = $request->query('collection');
        $searchFilter = $request->query('search');

        if ($collectionFilter) {
            $entries = $entries->where('collection', $collectionFilter)->values();
        }

        if ($searchFilter) {
            $entries = $entries->filter(
                fn ($entry) => str_contains(strtolower($entry['title']), strtolower($searchFilter))
            )->values();
        }

        return view('statamic-indexnow::utilities.index', [
            'entries' => $entries,
            'collections' => $collections,
            'configured' => ! empty($key),
            'production_url' => $productionUrl,
            'auto_submit' => config('statamic-indexnow.auto_submit', false),
            'collection_filter' => $collectionFilter,
            'search_filter' => $searchFilter,
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $request->validate([
            'urls' => 'required|array|min:1',
            'urls.*.url' => 'required|url',
            'urls.*.entry_id' => 'required|string',
        ]);

        $key = config('statamic-indexnow.key');

        if (empty($key)) {
            return back()->withErrors([
                'urls' => 'IndexNow API key is not configured. Set INDEXNOW_KEY in your .env file.',
            ]);
        }

        $urlEntries = $request->input('urls');
        $client = app(IndexNowClient::class);
        $result = $client->submit($urlEntries);

        if ($result['failed'] > 0) {
            return back()->withErrors([
                'urls' => implode(' | ', $result['errors']),
            ]);
        }

        return back()->with('success', "{$result['submitted']} URL(s) submitted to IndexNow successfully.");
    }
}
