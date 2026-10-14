<?php

namespace Database\Seeders;

use App\Models\PathNode;
use Illuminate\Database\Seeder;

/**
 * The five turns of the about page's timeline. Re-runnable: clears the table
 * first, so `db:seed --class=PathNodeSeeder` is idempotent.
 */
class PathNodeSeeder extends Seeder
{
    public function run(): void
    {
        PathNode::query()->delete();

        $nodes = [
            [
                'year' => ['en' => '2019', 'cs' => '2019'],
                'title' => ['en' => 'First touch with IT', 'cs' => 'První dotek s IT'],
                'body' => [
                    'en' => 'Took an IT extended class — the first touch with the world of IT, the touch that decided my career. Ever since, I have been hooked on technology.',
                    'cs' => 'Nastoupil jsem do třídy s rozšířenou výukou IT — první dotek se světem IT, dotek, který rozhodl o mé kariéře. Od té doby jsem na technologiích závislý.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2020', 'cs' => '2020'],
                'title' => ['en' => 'First website, then no website', 'cs' => 'První web, pak žádný web'],
                'body' => [
                    'en' => 'I fell for the idea that you can just build things. Shipped my first site, lost it to a dead hosting account, and learned about backups the expensive way.',
                    'cs' => 'Nadchla mě představa, že věci se dají prostě postavit. Vydal jsem první web, přišel o něj se zrušeným hostingem a naučil se o zálohách tou dražší cestou.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2022', 'cs' => '2022'],
                'title' => ['en' => 'SPŠE Pardubice', 'cs' => 'SPŠE Pardubice'],
                'body' => [
                    'en' => 'Chose the school that would keep pushing me. Backend clicked here — Java first, then everything around it.',
                    'cs' => 'Vybral jsem si školu, která mě nenechá usnout. Tady mi zapadl backend — nejdřív Java, pak všechno kolem ní.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => '2024', 'cs' => '2024'],
                'title' => ['en' => 'Competitions and a jury to face', 'cs' => 'Soutěže a porota před očima'],
                'body' => [
                    'en' => 'Hackathon wins, a national final, and the first time I had to explain my own architecture to strangers under a clock.',
                    'cs' => 'Výhry na hackathonech, celostátní finále a poprvé jsem musel vysvětlovat vlastní architekturu cizím lidem s hodinami v zádech.',
                ],
                'is_now' => false,
            ],
            [
                'year' => ['en' => 'Now', 'cs' => 'Teď'],
                'title' => ['en' => 'Freelance and Prezz', 'cs' => 'Freelance a Prezz'],
                'body' => [
                    'en' => 'Paid client work, a web dev group to help lead, and a platform in production that people use without knowing my name.',
                    'cs' => 'Placená práce pro klienty, vývojářská skupina, kterou pomáhám vést, a platforma v produkci, kterou lidé používají, aniž by znali moje jméno.',
                ],
                'is_now' => true,
            ],
        ];

        foreach ($nodes as $i => $node) {
            PathNode::create([...$node, 'sort_order' => $i]);
        }
    }
}
