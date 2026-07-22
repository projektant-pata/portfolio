<?php

namespace Database\Factories;

use App\Models\AboutCard;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AboutCard>
 */
class AboutCardFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => ['en' => $this->faker->words(2, true)],
            'text' => ['en' => $this->faker->paragraph()],
            'sort_order' => 0,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'title' => ['en' => $this->faker->words(2, true), 'cs' => $this->faker->words(2, true)],
            'text' => ['en' => $this->faker->paragraph(), 'cs' => $this->faker->paragraph()],
        ]);
    }
}
