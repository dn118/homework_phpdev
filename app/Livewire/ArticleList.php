<?php

namespace App\Livewire;

use App\Models\Article;
use App\Models\UserArticlePreference;
use App\Providers\HackerNewsArticleProvider;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ArticleList extends Component
{
    public ?string $syncError = null;

    public function mount(): void
    {
        if (Article::count() === 0) {
            $this->syncArticles();
        }
    }

    public function hide(string $articleId): void
    {
        $user = Auth::user();

        $pref = UserArticlePreference::where('user_id', $user->id)
            ->where('article_id', $articleId)
            ->first();

        if ($pref) {
            $pref->update(['hidden_at' => now()]);
        } else {
            UserArticlePreference::create([
                'user_id' => $user->id,
                'article_id' => $articleId,
                'hidden_at' => now(),
            ]);
        }
    }

    public function favorite(string $articleId): void
    {
        $user = Auth::user();

        $pref = UserArticlePreference::where('user_id', $user->id)
            ->where('article_id', $articleId)
            ->first();

        if ($pref && $pref->favorited_at) {
            $pref->update(['favorited_at' => null]);
        } else {
            UserArticlePreference::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'article_id' => $articleId,
                ],
                [
                    'favorited_at' => now(),
                ]
            );
        }
    }

    #[Computed]
    public function articles()
    {
        $user = Auth::user();

        $articles = Article::orderByDesc('published_at')->get();

        $preferences = UserArticlePreference::where('user_id', $user->id)
            ->get()
            ->keyBy('article_id');

        $articles = $articles->map(function ($article) use ($preferences) {
            $pref = $preferences->get($article->id);
            $article->is_hidden = $pref?->hidden_at !== null;
            $article->is_favorited = $pref?->favorited_at !== null;
            $article->favorited_at = $pref?->favorited_at;

            return $article;
        });

        $favorites = $articles->filter(fn ($a) => $a->is_favorited && ! $a->is_hidden)
            ->sortByDesc('favorited_at');

        $regular = $articles->filter(fn ($a) => ! $a->is_favorited && ! $a->is_hidden);

        return $favorites->merge($regular)->values();
    }

    private function syncArticles(): void
    {
        try {
            $articleProvider = app(HackerNewsArticleProvider::class);
            $articleProvider->fetchAndCacheArticles(config('articles.hackernews.sync_count', 30));
        } catch (\Throwable $exception) {
            $this->syncError = 'Articles could not be synced right now. Please try again later.';
        }
    }

    #[Computed]
    public function source(): string
    {
        return app(HackerNewsArticleProvider::class)->getSource();
    }

    public function render()
    {
        return view('livewire.article-list');
    }
}
