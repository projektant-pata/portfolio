<?php

namespace Database\Factories;

use App\Models\Badge;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Badge>
 */
class BadgeFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(1, 9999),
            'name' => ['en' => ucfirst($name)],
            'color' => $this->faker->randomElement(['red', 'blue', 'green', 'yellow', 'purple', 'zinc']),
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'name' => ['en' => ucfirst($this->faker->word()), 'cs' => ucfirst($this->faker->word())],
        ]);
    }
}
