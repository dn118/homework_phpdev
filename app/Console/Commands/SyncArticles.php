<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Providers\HackerNewsArticleProvider;
use Illuminate\Console\Command;

class SyncArticles extends Command
{
    protected $signature = 'articles:sync {--count=30 : Number of articles to fetch}';

    protected $description = 'Sync articles from Hacker News API';

    public function handle(): int
    {
        $count = (int) $this->option('count');

        $this->info('Syncing articles from Hacker News...');

        $beforeCount = Article::count();

        try {
            app(HackerNewsArticleProvider::class)->fetchAndCacheArticles($count);
        } catch (\Throwable $exception) {
            $this->error('Failed to sync articles: '.$exception->getMessage());

            return self::FAILURE;
        }

        $afterCount = Article::count();
        $createdCount = max(0, $afterCount - $beforeCount);

        if ($createdCount > 0) {
            $this->info('Created '.$createdCount.' new articles.');
        } else {
            $this->info('No new articles to create.');
        }

        $this->info('Sync complete. Total articles: '.$afterCount);

        return self::SUCCESS;
    }
}
