<?php

namespace Database\Factories;

use App\Models\Stat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stat>
 */
class StatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'value' => ['en' => (string) $this->faker->numberBetween(1, 99)],
            'text' => ['en' => $this->faker->words(2, true)],
            'value_id' => null,
            'source' => null,
            'sort_order' => 0,
            'tier' => 'normal',
            'note' => null,
        ];
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'value' => ['en' => 'Junior', 'cs' => 'Junior'],
            'text' => ['en' => $this->faker->words(2, true), 'cs' => $this->faker->words(2, true)],
        ]);
    }

    public function hero(): static
    {
        return $this->state(fn () => [
            'tier' => 'hero',
            'note' => ['en' => $this->faker->sentence(), 'cs' => $this->faker->sentence()],
        ]);
    }

    public function soft(): static
    {
        return $this->state(fn () => ['tier' => 'soft']);
    }
}
