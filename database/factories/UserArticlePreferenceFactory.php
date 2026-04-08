<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use App\Models\UserArticlePreference;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserArticlePreferenceFactory extends Factory
{
    protected $model = UserArticlePreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'article_id' => Article::factory(),
            'hidden_at' => null,
            'favorited_at' => null,
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'hidden_at' => now(),
            'favorited_at' => null,
        ]);
    }

    public function favorited(): static
    {
        return $this->state(fn (array $attributes) => [
            'hidden_at' => null,
            'favorited_at' => now(),
        ]);
    }
}
