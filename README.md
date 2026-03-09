# IndexNow for Statamic

Submit URLs to [IndexNow](https://www.indexnow.org/) directly from your Statamic Control Panel for faster search engine indexing.

## What is IndexNow?

[IndexNow](https://www.indexnow.org/) is an open protocol that allows websites to instantly notify search engines about content changes. Instead of waiting for search engines to discover updates through crawling, IndexNow enables real-time notifications resulting in faster indexing and more current search results.

When you submit to any one participating search engine, the submission is automatically shared with all others:

- **Bing**
- **Yandex**
- **Seznam**
- **Naver**

> **Note:** Google does not participate in IndexNow. Use [Google Search Console](https://search.google.com/search-console) for Google indexing.

## Features

- **CP Utility** — browse all published entries and submit selected URLs with one click
- **Submission tracking** — see which entries have been submitted and whether they've been modified since
- **Auto-submit on publish** — optionally submit URLs automatically when entries are saved
- **Bulk actions** — quickly select all unsubmitted or modified entries
- **Collection filtering** — filter entries by collection and search by title
- **Sortable columns** — sort by title, collection, status, last modified, or last submitted
- **Batch support** — large submissions are automatically chunked per IndexNow's 10,000 URL limit
- **History cleanup** — artisan command to prune old submission records

## Requirements

- Statamic 5
- Laravel 11 or 12

## Installation

```bash
composer require artofwifi/statamic-indexnow
```

No database required. Submission history is stored as a JSON file in `storage/statamic-indexnow/`.

## Setup

### 1. Generate an IndexNow key

Your API key must be 8-128 characters long, using alphanumeric characters and hyphens. You can generate one at [indexnow.org/genkey](https://www.indexnow.org/genkey) or use OpenSSL:

```bash
openssl rand -hex 32
```

### 2. Add the key to your `.env`

```env
INDEXNOW_KEY=your-key-here
```

### 3. Host the verification key file

IndexNow requires a verification file at your domain root to prove ownership. Create a text file named `{your-key}.txt` containing just the key:

```
https://yourdomain.com/your-key-here.txt
```

The file must be publicly accessible and return only the key as plain text.

That's it! Head to **CP > Utilities > IndexNow** and start submitting.

## Configuration

Publish the config file if you need to customize settings:

```bash
php artisan vendor:publish --tag=statamic-indexnow-config
```

This will create `config/statamic-indexnow.php` with the following options:

```php
return [
    // Your IndexNow API key
    'key' => env('INDEXNOW_KEY'),

    // IndexNow endpoint (any participating engine shares with all others)
    'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),

    // Base URL for submitted entries (useful when submitting from a dev environment)
    'production_url' => env('INDEXNOW_PRODUCTION_URL', env('APP_URL')),

    // Automatically submit URLs when entries are published or updated
    'auto_submit' => env('INDEXNOW_AUTO_SUBMIT', false),

    // Collection handles to exclude from the utility and auto-submission
    'exclude_collections' => [],

    // Max URLs per API request (IndexNow protocol limit is 10,000)
    'batch_size' => 10000,
];
```

### Production URL

If you manage your site from a development or staging environment, set `INDEXNOW_PRODUCTION_URL` to ensure production URLs are submitted regardless of which environment the CP runs in:

```env
INDEXNOW_PRODUCTION_URL=https://yourdomain.com
```

### Excluding collections

Add collection handles to `exclude_collections` to hide them from the utility and skip them during auto-submission:

```php
'exclude_collections' => ['internal', 'drafts'],
```

## Usage

### CP Utility

Navigate to **Utilities > IndexNow** in the Control Panel. You'll see all published entries with their submission status:

| Status | Meaning |
|---|---|
| **Never** (gray) | Entry has never been submitted to IndexNow |
| **Modified** (amber) | Entry was modified after the last submission |
| **Submitted** (green) | Entry hasn't changed since last submission |

Select entries and click **Submit Selected to IndexNow**. Use the quick-select buttons to select all unsubmitted or modified entries.

The table supports sorting by title, collection, status, last modified, and last submitted date. Filter by collection or search by title using the controls above the table.

### Auto-submit on publish

Enable automatic submission whenever entries are saved:

```env
INDEXNOW_AUTO_SUBMIT=true
```

When enabled, each published entry triggers a submission to IndexNow. Entries in excluded collections and unpublished entries are skipped.

### Cleaning up old records

Submission history accumulates over time. Use the artisan command to prune old records:

```bash
# Delete records older than 90 days (default)
php artisan indexnow:prune

# Delete records older than 30 days
php artisan indexnow:prune --days=30
```

To run this automatically, add it to your schedule in `routes/console.php` or `app/Console/Kernel.php`:

```php
$schedule->command('indexnow:prune')->monthly();
```

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.
