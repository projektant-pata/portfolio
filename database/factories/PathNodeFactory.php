<?php

namespace Database\Factories;

use App\Models\PathNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PathNode>
 */
class PathNodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'year' => ['en' => (string) $this->faker->year()],
            'title' => ['en' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence()],
            'is_now' => false,
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'year' => ['en' => 'Now', 'cs' => 'Teď'],
            'title' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
            'body' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }

    public function now(): static
    {
        return $this->state(fn () => ['is_now' => true]);
    }
}
