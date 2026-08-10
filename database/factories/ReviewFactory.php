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
            'highlight' => null,
            'source' => null,
            'source_color' => null,
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

    public function withHighlight(): static
    {
        return $this->state(function () {
            $word = $this->faker->word();

            return [
                'text' => ['en' => "\"Great work, especially the {$word}.\""],
                'highlight' => ['en' => $word],
                'source' => $this->faker->randomElement(['LinkedIn', 'E-mail', 'Reference']),
                'source_color' => $this->faker->randomElement(['#60A5FA', '#34D399', '#818CF8']),
            ];
        });
    }
}
