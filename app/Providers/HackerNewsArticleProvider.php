<?php

namespace App\Providers;

use App\Models\Article;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class HackerNewsArticleProvider
{
    public function fetchAndCacheArticles(int $count = 30): Collection
    {
        $baseUrl = config('articles.hackernews.base_url');
        $timeout = config('articles.hackernews.timeout', 10);

        $topStoriesUrl = $baseUrl.'topstories.json';
        $storyIds = Http::timeout($timeout)->get($topStoriesUrl)->json();

        if (! is_array($storyIds)) {
            return Article::all();
        }

        $storyIds = array_slice($storyIds, 0, $count);
        $articlesToUpsert = [];
        $timestamp = now();

        $responses = Http::pool(fn (Pool $pool) => collect($storyIds)
            ->mapWithKeys(fn ($id) => [
                (string) $id => $pool->as((string) $id)->timeout($timeout)->get($baseUrl.'item/'.$id.'.json'),
            ])
            ->all());

        foreach ($storyIds as $id) {
            $externalKey = (string) $id;
            $response = $responses[$externalKey] ?? null;
            $articleData = $response?->successful() ? $response->json() : null;

            if (! is_array($articleData) || ! isset($articleData['title'])) {
                continue;
            }

            $normalized = $this->normalizeArticle($externalKey, $articleData);
            $normalized['created_at'] = $timestamp;
            $normalized['updated_at'] = $timestamp;
            $articlesToUpsert[] = $normalized;
        }

        if (! empty($articlesToUpsert)) {
            Article::upsert(
                $articlesToUpsert,
                ['external_key'],
                ['title', 'url', 'source', 'description', 'published_at', 'payload', 'updated_at']
            );
        }

        return Article::orderByDesc('published_at')->get();
    }

    private function normalizeArticle(string $externalKey, array $data): array
    {
        return [
            'external_key' => $externalKey,
            'title' => $data['title'] ?? 'No Title',
            'url' => $data['url'] ?? null,
            'source' => 'Hacker News',
            'description' => null,
            'published_at' => isset($data['time']) ? date('Y-m-d H:i:s', $data['time']) : null,
            'payload' => json_encode($data),
        ];
    }

    public function getSource(): string
    {
        return config('articles.hackernews.base_url');
    }
}
