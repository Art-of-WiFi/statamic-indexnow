<?php

return [

    /*
    |--------------------------------------------------------------------------
    | IndexNow API Key
    |--------------------------------------------------------------------------
    |
    | The API key used to authenticate with IndexNow. Must be 8-128 characters
    | (a-z, A-Z, 0-9, hyphens). A matching {key}.txt file must be hosted at
    | your production domain root.
    |
    */

    'key' => env('INDEXNOW_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Search Engine Endpoint
    |--------------------------------------------------------------------------
    |
    | The IndexNow endpoint to submit URLs to. Any participating engine will
    | share submissions with all others (Bing, Yandex, Seznam, Naver).
    |
    */

    'endpoint' => env('INDEXNOW_ENDPOINT', 'https://api.indexnow.org/indexnow'),

    /*
    |--------------------------------------------------------------------------
    | Production URL
    |--------------------------------------------------------------------------
    |
    | The base URL used when building entry URLs for submission. This ensures
    | production URLs are submitted even when triggered from a dev environment.
    | Falls back to APP_URL if not set.
    |
    */

    'production_url' => env('INDEXNOW_PRODUCTION_URL', env('APP_URL')),

    /*
    |--------------------------------------------------------------------------
    | Auto-Submit on Publish
    |--------------------------------------------------------------------------
    |
    | When enabled, URLs are automatically submitted to IndexNow whenever an
    | entry is published or updated. Only submits if the entry's collection
    | is not in the exclude list and a valid key is configured.
    |
    */

    'auto_submit' => env('INDEXNOW_AUTO_SUBMIT', false),

    /*
    |--------------------------------------------------------------------------
    | Excluded Collections
    |--------------------------------------------------------------------------
    |
    | Collections listed here will be excluded from the IndexNow utility and
    | from auto-submission. Use collection handles (e.g. 'drafts', 'internal').
    |
    */

    'exclude_collections' => [],

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    |
    | Maximum number of URLs to include in a single IndexNow API request.
    | The IndexNow protocol allows up to 10,000 URLs per request.
    |
    */

    'batch_size' => 10000,

];
