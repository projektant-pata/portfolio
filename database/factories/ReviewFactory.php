<?php

namespace Database\Factories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'position' => ['en' => $this->faker->jobTitle()],
            'text' => ['en' => '"'.$this->faker->sentence().'"'],
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'position' => ['en' => $this->faker->jobTitle(), 'cs' => $this->faker->jobTitle()],
            'text' => ['en' => '"'.$this->faker->sentence().'"', 'cs' => '"'.$this->faker->sentence().'"'],
        ]);
    }
}
