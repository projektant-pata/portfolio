<?php

namespace Database\Factories;

use App\Models\Experience;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Experience>
 */
class ExperienceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['work', 'life']),
            'is_special' => false,
            'title' => ['en' => $this->faker->jobTitle()],
            'subtitle' => ['en' => $this->faker->company()],
            'content' => null,
            'year' => ['en' => (string) $this->faker->year()],
            'image_path' => null,
            'links' => null,
            'sort_order' => 0,
        ];
    }

    public function withTranslatedYear(): static
    {
        return $this->state(fn () => [
            'year' => ['en' => '2022 – present', 'cs' => '2022 – nyní'],
        ]);
    }

    public function withContent(): static
    {
        return $this->state(fn () => [
            'content' => ['en' => $this->faker->paragraphs(2, true)],
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'title' => ['en' => $this->faker->jobTitle(), 'cs' => $this->faker->jobTitle()],
            'subtitle' => ['en' => $this->faker->company(), 'cs' => $this->faker->company()],
            'content' => ['en' => $this->faker->paragraph(), 'cs' => $this->faker->paragraph()],
        ]);
    }
}
