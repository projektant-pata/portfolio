<?php

namespace Database\Factories;

use App\Models\Interest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Interest>
 */
class InterestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ['en' => $this->faker->words(3, true)],
            'highlight' => [],
            'body' => ['en' => $this->faker->sentence()],
            'meta' => ['en' => $this->faker->words(2, true)],
            'image_path' => null,
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'title' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
            'meta' => ['en' => $this->faker->words(2, true), 'cs' => $this->faker->words(2, true)],
        ]);
    }
}
