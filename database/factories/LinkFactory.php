<?php

namespace Database\Factories;

use App\Models\Link;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Link>
 */
class LinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'url' => $this->faker->url(),
            'kind' => 'live',
            'alt' => ['en' => $this->faker->words(3, true)],
            'img_url' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'alt' => ['en' => $this->faker->words(3, true), 'cs' => $this->faker->words(3, true)],
        ]);
    }
}
