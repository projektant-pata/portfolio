<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

/**
 * The toolbox behind both grids: `grid` rows fill the four about-me columns
 * (and the home 4×2 cells when flagged), `rotation` and `learning` rows are
 * the chips in the two footers. Re-runnable: clears the table first.
 */
class ToolSeeder extends Seeder
{
    public function run(): void
    {
        Tool::query()->delete();

        $backend = ['en' => 'Backend', 'cs' => 'Backend'];
        $frontend = ['en' => 'Frontend', 'cs' => 'Frontend'];
        $infra = ['en' => 'Data & infra', 'cs' => 'Data a infrastruktura'];
        $hardware = ['en' => 'Hardware & homelab', 'cs' => 'Hardware a homelab'];

        $tools = [
            ['name' => 'Spring Boot', 'color' => '#6DB33F', 'frequency' => 'daily', 'group' => $backend, 'show_on_home' => true],
            ['name' => 'Java', 'color' => '#F89820', 'frequency' => 'daily', 'group' => $backend, 'show_on_home' => true],
            ['name' => 'Laravel', 'color' => '#FF2D20', 'frequency' => 'often', 'group' => $backend, 'show_on_home' => true],
            ['name' => 'PHP', 'color' => '#777BB4', 'frequency' => 'often', 'group' => $backend],
            ['name' => 'REST APIs', 'color' => '#A3A3A3', 'frequency' => 'daily', 'group' => $backend],

            ['name' => 'TypeScript', 'color' => '#3178C6', 'frequency' => 'daily', 'group' => $frontend, 'show_on_home' => true],
            ['name' => 'JavaScript', 'color' => '#F7DF1E', 'frequency' => 'daily', 'group' => $frontend],
            ['name' => 'HTML & CSS', 'color' => '#E34F26', 'frequency' => 'daily', 'group' => $frontend],
            ['name' => 'Tailwind', 'color' => '#06B6D4', 'frequency' => 'often', 'group' => $frontend],
            ['name' => 'Figma', 'color' => '#F24E1E', 'frequency' => 'sometimes', 'group' => $frontend, 'show_on_home' => true],

            ['name' => 'PostgreSQL', 'color' => '#34D399', 'frequency' => 'daily', 'group' => $infra, 'show_on_home' => true],
            ['name' => 'MySQL', 'color' => '#00758F', 'frequency' => 'often', 'group' => $infra],
            ['name' => 'Docker', 'color' => '#2496ED', 'frequency' => 'often', 'group' => $infra, 'show_on_home' => true],
            ['name' => 'Arch Linux', 'color' => '#1793D1', 'frequency' => 'daily', 'group' => $infra, 'show_on_home' => true],
            ['name' => 'Git', 'color' => '#F05032', 'frequency' => 'daily', 'group' => $infra],

            ['name' => 'Raspberry Pi', 'color' => '#F59E0B', 'frequency' => 'often', 'group' => $hardware],
            ['name' => 'Home Assistant', 'color' => '#818CF8', 'frequency' => 'often', 'group' => $hardware],
            ['name' => 'Proxmox', 'color' => '#A3A3A3', 'frequency' => 'sometimes', 'group' => $hardware],
            ['name' => 'Sensors / MQTT', 'color' => '#34D399', 'frequency' => 'sometimes', 'group' => $hardware],
            ['name' => 'Soldering iron', 'color' => '#EAB308', 'frequency' => 'rarely', 'group' => $hardware],

            ['name' => 'GitHub', 'placement' => 'rotation'],
            ['name' => 'VS Code', 'placement' => 'rotation'],
            ['name' => 'Claude Code', 'placement' => 'rotation'],

            ['name' => 'Kubernetes', 'placement' => 'learning'],
            ['name' => 'Kotlin', 'placement' => 'learning'],
            ['name' => 'Grafana', 'placement' => 'learning'],
        ];

        foreach ($tools as $index => $tool) {
            Tool::create([
                'name' => $tool['name'],
                'color' => $tool['color'] ?? null,
                'frequency' => $tool['frequency'] ?? null,
                'group' => $tool['group'] ?? null,
                'placement' => $tool['placement'] ?? 'grid',
                'show_on_home' => $tool['show_on_home'] ?? false,
                'sort_order' => $index,
            ]);
        }
    }
}
