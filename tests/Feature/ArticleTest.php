<?php

namespace Tests\Feature;

use App\Livewire\ArticleList;
use App\Models\Article;
use App\Models\User;
use App\Models\UserArticlePreference;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
    }

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

    public function test_user_can_verify_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $response->assertRedirect('/dashboard?verified=1');

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/verify-email');
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

    public function test_empty_state_explains_how_articles_are_populated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->assertSee('No visible articles right now.')
            ->assertSee('Run `php artisan articles:sync` or restart the local server to populate articles.');

        $this->assertDatabaseCount('articles', 0);
    }

    public function test_hacker_news_native_posts_show_discussion_link(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create([
            'external_key' => '12345',
            'title' => 'Ask HN Example',
            'url' => null,
        ]);

        Livewire::actingAs($user)->test(ArticleList::class)
            ->assertSee($article->title)
            ->assertSee('Open Hacker News discussion')
            ->assertSee('https://news.ycombinator.com/item?id=12345');
    }

    public function test_hidden_article_can_be_restored(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();

        Livewire::actingAs($user)->test(ArticleList::class)
            ->call('hide', (string) $article->id)
            ->call('unhide', (string) $article->id);

        $this->assertDatabaseHas('user_article_preferences', [
            'user_id' => $user->id,
            'article_id' => $article->id,
            'hidden_at' => null,
        ]);
    }

    public function test_user_can_request_password_reset_link(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertSessionHas('status');
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create();
        $newPassword = 'NewPassword123!';

        $token = Password::createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response->assertRedirect('/login');
        $this->assertTrue(Hash::check($newPassword, $user->fresh()->password));
    }

    public function test_user_can_login_with_new_password_after_reset(): void
    {
        $user = User::factory()->create();
        $newPassword = 'NewPassword123!';

        $token = Password::createToken($user);

        $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => $newPassword,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }
}
