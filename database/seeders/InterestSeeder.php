<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

/**
 * The three "off the screen" cells. `image_path` is null until the real
 * photographs land — the page renders the placeholder plate meanwhile.
 * Re-runnable: clears the table first.
 */
class InterestSeeder extends Seeder
{
    public function run(): void
    {
        Interest::query()->delete();

        $interests = [
            [
                'title' => ['en' => 'Chess since 2nd grade', 'cs' => 'Šachy od druhé třídy'],
                'highlight' => ['en' => '2nd grade', 'cs' => 'druhé třídy'],
                'body' => [
                    'en' => 'Local tournaments against high-schoolers taught me to think three moves out and to lose without falling apart. The knight is the piece.',
                    'cs' => 'Místní turnaje proti středoškolákům mě naučily myslet tři tahy dopředu a prohrávat bez zhroucení. Jezdec je ta figura.',
                ],
                'meta' => ['en' => '1684 elo · patience, strategy', 'cs' => '1684 elo · trpělivost, strategie'],
            ],
            [
                'title' => ['en' => 'Rock, loud', 'cs' => 'Rock, nahlas'],
                'highlight' => ['en' => 'loud', 'cs' => 'nahlas'],
                'body' => [
                    'en' => 'Most of my code was written with a guitar in one ear. Live shows whenever the calendar lets me.',
                    'cs' => 'Většina mého kódu vznikla s kytarou v jednom uchu. Koncerty vždycky, když to kalendář dovolí.',
                ],
                'meta' => ['en' => 'Concerts · playlists · headphones', 'cs' => 'Koncerty · playlisty · sluchátka'],
            ],
            [
                'title' => ['en' => 'Homelab and catfish', 'cs' => 'Homelab a sumci'],
                'highlight' => ['en' => 'catfish', 'cs' => 'sumci'],
                'body' => [
                    'en' => 'A rack of Raspberry Pis I break on purpose, and yes — catfish. Both are hobbies I get asked about more than my job.',
                    'cs' => 'Stojan Raspberry Pi, které rozbíjím schválně, a ano — sumci. Na obojí se mě lidé ptají víc než na práci.',
                ],
                'meta' => ['en' => 'Sensors · self-hosting · fishing', 'cs' => 'Senzory · self-hosting · rybaření'],
            ],
        ];

        foreach ($interests as $i => $interest) {
            Interest::create([...$interest, 'image_path' => null, 'sort_order' => $i]);
        }
    }
}
