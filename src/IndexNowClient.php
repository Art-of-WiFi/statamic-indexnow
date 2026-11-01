<?php

namespace ArtOfWifi\StatamicIndexnow;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class IndexNowClient
{
    /**
     * Submit URLs to the IndexNow API in batches.
     *
     * @param  array<int, array{entry_id: string, url: string}>  $urlEntries
     * @return array{submitted: int, failed: int, errors: list<string>}
     */
    public function submit(array $urlEntries): array
    {
        $key = config('statamic-indexnow.key');
        $productionUrl = config('statamic-indexnow.production_url');
        $endpoint = config('statamic-indexnow.endpoint');
        $batchSize = config('statamic-indexnow.batch_size', 10000);

        $host = parse_url($productionUrl, PHP_URL_HOST);
        $chunks = array_chunk($urlEntries, $batchSize);
        $batchId = Str::uuid()->toString();
        $store = app(SubmissionStore::class);

        $submitted = 0;
        $failed = 0;
        $errors = [];

        foreach ($chunks as $chunk) {
            $urls = array_column($chunk, 'url');

            $response = Http::acceptJson()->post($endpoint, [
                'host' => $host,
                'key' => $key,
                'keyLocation' => rtrim($productionUrl, '/') . "/{$key}.txt",
                'urlList' => array_values($urls),
            ]);

            $statusCode = $response->status();

            $store->record($chunk, $statusCode, $batchId);

            if ($response->successful()) {
                $submitted += count($chunk);
            } else {
                $failed += count($chunk);
                $errors[] = "HTTP {$statusCode}: {$response->body()}";
            }
        }

        return [
            'submitted' => $submitted,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Submit a single URL to IndexNow.
     *
     * @return array{submitted: int, failed: int, errors: list<string>}
     */
    public function submitSingle(string $entryId, string $url): array
    {
        return $this->submit([
            ['entry_id' => $entryId, 'url' => $url],
        ]);
    }

    /**
     * Build a production URL for an entry.
     */
    public function buildProductionUrl(string $uri): string
    {
        $productionUrl = config('statamic-indexnow.production_url');

        if ($uri === '/' || $uri === '') {
            return $productionUrl;
        }

        return rtrim($productionUrl, '/') . $uri;
    }
}
