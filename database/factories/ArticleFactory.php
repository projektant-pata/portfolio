<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $header = $this->faker->sentence(4);

        return [
            'slug' => Str::slug($header).'-'.$this->faker->unique()->numberBetween(1, 9999),
            'header' => ['en' => $header],
            'description' => ['en' => $this->faker->sentence()],
            'content' => ['en' => $this->faker->paragraphs(3, true)],
            'date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'thumbnail_url' => null,
            'user_id' => User::factory(),
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'header' => ['en' => $this->faker->sentence(4), 'cs' => $this->faker->sentence(4)],
            'description' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
            'content' => ['en' => $this->faker->paragraph(), 'cs' => $this->faker->paragraph()],
        ]);
    }
}
