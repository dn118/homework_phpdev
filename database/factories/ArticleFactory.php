<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'external_key' => $this->faker->unique()->uuid(),
            'title' => $this->faker->sentence(),
            'url' => $this->faker->url(),
            'source' => 'Hacker News',
            'description' => $this->faker->optional()->paragraph(),
            'published_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'payload' => ['score' => $this->faker->numberBetween(1, 500), 'by' => $this->faker->userName()],
        ];
    }
}
