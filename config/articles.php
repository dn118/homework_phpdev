<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Article Provider Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the article provider that syncs articles from public APIs.
    | Currently configured for Hacker News Firebase API.
    |
    */

    'hackernews' => [
        'base_url' => env('HACKERNEWS_BASE_URL', 'https://hacker-news.firebaseio.com/v0/'),
        'sync_count' => env('ARTICLES_SYNC_COUNT', 30),
        'timeout' => env('ARTICLES_TIMEOUT', 10),
    ],

];
