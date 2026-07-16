<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    public function definition(): array
    {
        $header = $this->faker->sentence(3);

        return [
            'year' => $this->faker->numberBetween(2018, 2026),
            'slug' => Str::slug($header) . '-' . $this->faker->unique()->numberBetween(1, 9999),
            'header' => ['en' => $header],
            'description' => ['en' => $this->faker->sentence()],
            'img_url' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'header' => ['en' => $this->faker->sentence(3), 'cs' => $this->faker->sentence(3)],
            'description' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }
}
