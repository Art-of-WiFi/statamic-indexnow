<?php

namespace ArtOfWifi\StatamicIndexnow;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class SubmissionStore
{
    protected string $path;

    public function __construct()
    {
        $this->path = storage_path('statamic-indexnow/submissions.json');
    }

    /**
     * Record a submission for the given entries.
     *
     * @param  array<int, array{entry_id: string, url: string}>  $entries
     */
    public function record(array $entries, int $statusCode, string $batchId): void
    {
        $submissions = $this->all();
        $now = now()->toIso8601String();

        foreach ($entries as $entry) {
            $submissions->push([
                'entry_id' => $entry['entry_id'],
                'url' => $entry['url'],
                'batch_id' => $batchId,
                'status_code' => $statusCode,
                'submitted_at' => $now,
            ]);
        }

        $this->save($submissions);
    }

    /**
     * Get the last submission timestamp per entry ID.
     *
     * @return Collection<string, string>
     */
    public function lastSubmittedPerEntry(): Collection
    {
        return $this->all()
            ->groupBy('entry_id')
            ->map(fn (Collection $records) => $records->max('submitted_at'));
    }

    /**
     * Prune submissions older than the given number of days.
     */
    public function prune(int $days): int
    {
        $submissions = $this->all();
        $cutoff = now()->subDays($days)->toIso8601String();

        $remaining = $submissions->filter(
            fn (array $record) => $record['submitted_at'] >= $cutoff
        );

        $deleted = $submissions->count() - $remaining->count();

        $this->save($remaining->values());

        return $deleted;
    }

    /**
     * Get all submission records.
     *
     * @return Collection<int, array{entry_id: string, url: string, batch_id: string, status_code: int, submitted_at: string}>
     */
    public function all(): Collection
    {
        if (! File::exists($this->path)) {
            return collect();
        }

        $data = json_decode(File::get($this->path), true);

        return collect($data ?? []);
    }

    protected function save(Collection $submissions): void
    {
        File::ensureDirectoryExists(dirname($this->path));
        File::put($this->path, json_encode($submissions->values()->all(), JSON_PRETTY_PRINT));
    }
}
