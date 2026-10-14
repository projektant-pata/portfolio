<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

/**
 * The toolbox behind both grids: `grid` rows fill the four about-me groups
 * (and the home cells when flagged), `rotation` and `learning` rows are the
 * chips in the two footers. The group note rides on the first row of its
 * group — it is what the group head prints beside its hairline.
 * Re-runnable: clears the table first.
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
        $creative = ['en' => 'Creative work', 'cs' => 'Kreativní práce'];
        $devtools = ['en' => 'Dev tools', 'cs' => 'Vývojářské nástroje'];

        $tools = [
            ['name' => 'Java (Spring Boot)', 'color' => '#F97316', 'state' => 'using', 'group' => $backend, 'show_on_home' => true, 'note' => ['en' => 'where I actually live', 'cs' => 'kde vážně bydlím']],
            ['name' => 'PHP (Laravel)', 'color' => '#FF2D20', 'state' => 'using', 'group' => $backend, 'show_on_home' => true],
            ['name' => 'TypeScript (Next.js)', 'color' => '#A1A1AA', 'state' => 'using', 'group' => $backend],

            ['name' => 'TypeScript (React)', 'color' => '#3178C6', 'state' => 'using', 'group' => $frontend, 'show_on_home' => true, 'note' => ['en' => 'enough to ship a whole thing', 'cs' => 'dost na to dodat celou věc']],
            ['name' => 'HTML & CSS', 'color' => '#38BDF8', 'state' => 'using', 'group' => $frontend],
            ['name' => 'Blade / Twig', 'color' => '#818CF8', 'state' => 'using', 'group' => $frontend],
            ['name' => 'Tailwind', 'color' => '#60A5FA', 'state' => 'using', 'group' => $frontend, 'show_on_home' => true],

            ['name' => 'MySQL', 'color' => '#34D399', 'state' => 'using', 'group' => $infra, 'note' => ['en' => 'learned by breaking my own servers', 'cs' => 'naučeno rozbíjením vlastních serverů']],
            ['name' => 'PostgreSQL', 'color' => '#22D3EE', 'state' => 'using', 'group' => $infra, 'show_on_home' => true],
            ['name' => 'Proxmox VE', 'color' => '#E57000', 'state' => 'using', 'group' => $infra, 'show_on_home' => true],

            ['name' => 'Raspberry Pi', 'color' => '#F59E0B', 'state' => 'experience', 'group' => $hardware, 'note' => ['en' => 'the test bench, not the job', 'cs' => 'testovací stůl, ne práce']],
            ['name' => '3D printing', 'color' => '#A3A3A3', 'state' => 'experience', 'group' => $hardware],
            ['name' => 'Soldering', 'color' => '#F87171', 'state' => 'experience', 'group' => $hardware],
            ['name' => 'Home automation', 'color' => '#FBBF24', 'state' => 'experience', 'group' => $hardware],

            ['name' => 'Figma', 'color' => '#A259FF', 'state' => 'using', 'group' => $creative, 'note' => ['en' => 'wireframes before a line of code', 'cs' => 'drátěné modely dřív než první řádek kódu']],
            ['name' => 'Kdenlive', 'color' => '#1E88E5', 'state' => 'using', 'group' => $creative],

            ['name' => 'GitHub', 'color' => '#9CA3AF', 'state' => 'using', 'group' => $devtools, 'note' => ['en' => 'daily driver, not a portfolio badge', 'cs' => 'denní chleba, ne odznak do portfolia']],
            ['name' => 'VS Code', 'color' => '#007ACC', 'state' => 'using', 'group' => $devtools],
            ['name' => 'Claude Code', 'color' => '#D97757', 'state' => 'using', 'group' => $devtools],
            ['name' => 'Arch Linux', 'color' => '#1793D1', 'state' => 'using', 'group' => $devtools],

            ['name' => 'Docker', 'placement' => 'rotation'],
        ];

        foreach ($tools as $index => $tool) {
            Tool::create([
                'name' => $tool['name'],
                'color' => $tool['color'] ?? null,
                'state' => $tool['state'] ?? null,
                'group' => $tool['group'] ?? null,
                'group_note' => $tool['note'] ?? null,
                'placement' => $tool['placement'] ?? 'grid',
                'show_on_home' => $tool['show_on_home'] ?? false,
                'sort_order' => $index,
            ]);
        }
    }
}
