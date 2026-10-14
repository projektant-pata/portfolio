<?php

namespace Database\Factories;

use App\Models\Tool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tool>
 */
class ToolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'color' => $this->faker->hexColor(),
            'state' => $this->faker->randomElement(Tool::STATES),
            'group' => ['en' => 'Backend'],
            'group_note' => null,
            'placement' => 'grid',
            'show_on_home' => false,
            'sort_order' => 0,
        ];
    }

    public function onHome(): static
    {
        return $this->state(fn () => ['show_on_home' => true]);
    }

    /** A chip in one of the two footers: no group, no state. */
    public function chip(string $placement = 'rotation'): static
    {
        return $this->state(fn () => [
            'placement' => $placement,
            'group' => null,
            'state' => null,
            'color' => null,
        ]);
    }

    public function translated(): static
    {
        return $this->state(fn () => [
            'group' => ['en' => 'Backend', 'cs' => 'Backend'],
        ]);
    }
}
