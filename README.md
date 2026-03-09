# IndexNow for Statamic

A Statamic addon that lets you submit URLs to [IndexNow](https://www.indexnow.org/) directly from the Control Panel for faster indexing by Bing, Yandex, Seznam, Naver, and other participating search engines.

## Features

- **CP Utility** — Browse all published entries and submit selected URLs to IndexNow with one click
- **Submission tracking** — See which entries have been submitted, when, and whether they've been modified since
- **Bulk actions** — Select all unsubmitted or modified entries with one click
- **Auto-submit on publish** — Optionally submit URLs automatically when entries are saved (configurable)
- **Collection filtering** — Filter and search entries by collection and title
- **Sortable columns** — Sort by title, collection, status, last modified, or last submitted
- **Exclude collections** — Hide specific collections from the utility and auto-submission
- **Batch support** — Large submissions are automatically chunked (IndexNow allows max 10,000 URLs per request)
- **History cleanup** — Artisan command to prune old submission records

## Requirements

- PHP 8.2+
- Statamic 5
- Laravel 11 or 12

## Installation

```bash
composer require artofwifi/statamic-indexnow
```

Run the migration:

```bash
php artisan migrate
```

## Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=statamic-indexnow-config
```

### Environment Variables

Add these to your `.env` file:

```env
INDEXNOW_KEY=your-indexnow-key-here
```

Optional:

```env
INDEXNOW_PRODUCTION_URL=https://yourdomain.com
INDEXNOW_AUTO_SUBMIT=false
INDEXNOW_ENDPOINT=https://api.indexnow.org/indexnow
```

### Setting Up Your IndexNow Key

1. Generate a key (8-128 alphanumeric characters with optional hyphens)
2. Add it to your `.env` as `INDEXNOW_KEY`
3. Create a text file named `{your-key}.txt` containing just the key
4. Host this file at the root of your domain: `https://yourdomain.com/{your-key}.txt`

### Production URL

If you manage your site from a development environment, set `INDEXNOW_PRODUCTION_URL` to your production domain. This ensures production URLs are submitted regardless of which environment you're using the CP from.

### Config Options

| Option | Default | Description |
|---|---|---|
| `key` | `null` | Your IndexNow API key |
| `endpoint` | `https://api.indexnow.org/indexnow` | IndexNow API endpoint |
| `production_url` | `APP_URL` | Base URL for submitted entries |
| `auto_submit` | `false` | Auto-submit when entries are saved |
| `exclude_collections` | `[]` | Collection handles to exclude |
| `batch_size` | `10000` | Max URLs per API request |

## Usage

### CP Utility

Navigate to **Utilities > IndexNow** in the Statamic Control Panel. You'll see a table of all published entries with their submission status:

- **Never** (gray) — Entry has never been submitted
- **Modified** (amber) — Entry was modified after the last submission
- **Submitted** (green) — Entry hasn't changed since last submission

Select entries and click **Submit Selected to IndexNow**.

Use the **Select unsubmitted** and **Select modified** buttons for quick bulk selection.

### Auto-Submit

Set `INDEXNOW_AUTO_SUBMIT=true` in your `.env` to automatically submit URLs when entries are published or updated. Entries in excluded collections are skipped.

### Pruning Old Records

Clean up old submission records:

```bash
# Delete records older than 90 days (default)
php artisan indexnow:prune

# Delete records older than 30 days
php artisan indexnow:prune --days=30
```

You can schedule this in your `app/Console/Kernel.php`:

```php
$schedule->command('indexnow:prune')->monthly();
```

## About IndexNow

IndexNow is an open protocol that allows websites to notify search engines about URL changes instantly. When you submit to one participating engine, all others are notified automatically.

**Participating search engines:** Bing, Yandex, Seznam, Naver

**Note:** Google does not participate in IndexNow. Use Google Search Console for Google indexing.

## License

MIT License. See [LICENSE.md](LICENSE.md) for details.
