<?php

namespace Tests\Feature;

use App\Livewire\ArticleList;
use App\Models\Article;
use App\Models\User;
use App\Models\UserArticlePreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_guest_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_favorite_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('favorite', (string) $article->id);

        $this->assertDatabaseHas('user_article_preferences', [
            'user_id' => $user->id,
            'article_id' => $article->id,
        ]);
    }

    public function test_authenticated_user_can_hide_article(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('hide', (string) $article->id);

        $this->assertDatabaseHas('user_article_preferences', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'hidden_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_hide_affects_only_current_user(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user1)->test(ArticleList::class)
            ->call('hide', (string) $article->id);

        $this->assertDatabaseMissing('user_article_preferences', [
            'user_id' => $user2->id,
            'article_id' => $article->id,
        ]);
    }

    public function test_favorites_are_ordered_by_latest_first(): void
    {
        $user = User::factory()->create();
        $article1 = Article::factory()->create(['title' => 'Article 1']);
        $article2 = Article::factory()->create(['title' => 'Article 2']);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('favorite', (string) $article2->id);

        sleep(1);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('favorite', (string) $article1->id);

        $prefs = UserArticlePreference::where('user_id', $user->id)
            ->orderBy('favorited_at', 'desc')
            ->get();

        $this->assertEquals($article1->id, $prefs->first()->article_id);
    }

    public function test_hiding_favorited_article_does_not_clear_favorite(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('favorite', (string) $article->id);

        $pref = UserArticlePreference::where('user_id', $user->id)
            ->where('article_id', $article->id)
            ->first();

        $this->assertNotNull($pref->favorited_at);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('hide', (string) $article->id);

        $pref->refresh();

        $this->assertNotNull($pref->favorited_at);
        $this->assertNotNull($pref->hidden_at);
    }

    public function test_favoriting_hidden_article_does_not_clear_hidden(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('hide', (string) $article->id);

        $pref = UserArticlePreference::where('user_id', $user->id)
            ->where('article_id', $article->id)
            ->first();

        $this->assertNotNull($pref->hidden_at);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('favorite', (string) $article->id);

        $pref->refresh();

        $this->assertNotNull($pref->favorited_at);
        $this->assertNotNull($pref->hidden_at);
    }

    public function test_articles_load_from_local_database(): void
    {
        $user = User::factory()->create();
        $article1 = Article::factory()->create(['title' => 'Article 1']);
        $article2 = Article::factory()->create(['title' => 'Article 2']);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->assertSet('articles', function ($articles) use ($article1, $article2) {
                $ids = $articles->pluck('id')->toArray();

                return in_array($article1->id, $ids) && in_array($article2->id, $ids);
            });
    }
}
