<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

/**
 * The four "off the screen" chapters. `image_path` is null until the real
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
                'label' => ['en' => 'Chess', 'cs' => 'Šachy'],
                'title' => ['en' => 'Chess since the second grade', 'cs' => 'Šachy od druhé třídy'],
                'highlight' => ['en' => 'second grade', 'cs' => 'druhé třídy'],
                'body' => [
                    'en' => "I started because a teacher needed one more kid to fill out the school team, and stayed because I was bad at it for a long time. Local tournaments meant sitting across from high-schoolers who had been playing longer than I had been alive, losing, and coming back the next Sunday anyway.\n\nIt is the only hobby I have that punishes impatience immediately. You cannot bluff a position — either you saw the consequence three moves ago or you did not, and the board tells you which within a minute. 1684 is my peak rating, which is respectable and nowhere near good, and I am fine with both halves of that sentence.",
                    'cs' => "Začal jsem, protože učiteli chyběl jeden hráč do školního týmu, a zůstal jsem, protože mi to dlouho nešlo. Místní turnaje znamenaly sedět naproti středoškolákům, kteří hráli déle, než jsem byl na světě, prohrávat a stejně přijít i příští neděli.\n\nJe to jediný koníček, který okamžitě trestá netrpělivost. Pozici nelze zablufovat — buď jste důsledek viděli tři tahy dopředu, nebo ne, a šachovnice vám to řekne do minuty. 1684 je moje nejvyšší rating, což je slušné a zdaleka ne dobré, a s oběma polovinami té věty jsem smířený.",
                ],
                'pull' => [
                    'en' => 'Chess is where I learned to lose something I cared about and still show up the following week.',
                    'cs' => 'Šachy mě naučily prohrát něco, na čem mi záleželo, a přesto přijít i příští týden.',
                ],
                'facts' => [
                    'en' => [
                        ['dt' => 'Peak rating', 'dd' => '1684 elo'],
                        ['dt' => 'Playing since', 'dd' => '2nd grade'],
                        ['dt' => 'Favourite piece', 'dd' => 'Knight'],
                    ],
                    'cs' => [
                        ['dt' => 'Nejvyšší rating', 'dd' => '1684 elo'],
                        ['dt' => 'Hraju od', 'dd' => '2. třídy'],
                        ['dt' => 'Oblíbená figura', 'dd' => 'Jezdec'],
                    ],
                ],
                'meta' => ['en' => [], 'cs' => []],
            ],
            [
                'label' => ['en' => 'Homelab', 'cs' => 'Homelab'],
                'title' => ['en' => 'A shelf of computers I break on purpose', 'cs' => 'Police počítačů, které schválně rozbíjím'],
                'highlight' => ['en' => 'break on purpose', 'cs' => 'schválně rozbíjím'],
                'body' => [
                    'en' => "Three Raspberry Pis and one small Proxmox box on a wooden shelf, wired badly, labelled worse. Between them they run my file storage, a monitoring stack that pages me when something falls over, and a set of MQTT sensors that log the temperature of a flat nobody asked me to measure.\n\nEverything I know about Linux, Docker and networking, I know because I broke this rack first. There is no staging environment at home — you take the service down, your own stuff stops working, and you fix it at 11pm because you want your files back. That is a faster teacher than any tutorial.",
                    'cs' => "Tři Raspberry Pi a jeden malý Proxmox box na dřevěné polici, špatně zapojené a ještě hůř popsané. Dohromady na nich běží moje úložiště souborů, monitorovací stack, který mě upozorní, když něco spadne, a sada MQTT senzorů, které loguju teplotu bytu, kterou po mně nikdo nechtěl.\n\nVšechno, co umím o Linuxu, Dockeru a síťování, umím proto, že jsem si tenhle rack nejdřív rozbil sám. Doma žádné staging prostředí není — vypnete službu, přestanou fungovat vlastní věci a opravujete to v jedenáct večer, protože chcete zpátky svoje soubory. To je rychlejší učitel než jakýkoliv tutoriál.",
                ],
                'pull' => ['en' => null, 'cs' => null],
                'facts' => [
                    'en' => [
                        ['dt' => 'Nodes', 'dd' => '3 Pis + 1 Proxmox host'],
                        ['dt' => 'Running', 'dd' => 'Storage · monitoring · MQTT'],
                        ['dt' => 'Rebuilt', 'dd' => 'More times than I admit'],
                    ],
                    'cs' => [
                        ['dt' => 'Uzly', 'dd' => '3× Pi + 1× Proxmox host'],
                        ['dt' => 'Běží tam', 'dd' => 'Úložiště · monitoring · MQTT'],
                        ['dt' => 'Přestavěno', 'dd' => 'Víckrát, než přiznávám'],
                    ],
                ],
                'meta' => ['en' => [], 'cs' => []],
            ],
            [
                'label' => ['en' => 'Sport', 'cs' => 'Sport'],
                'title' => ['en' => 'The gym is where I put the phone down', 'cs' => 'V posilovně odkládám telefon'],
                'highlight' => ['en' => 'put the phone down', 'cs' => 'odkládám telefon'],
                'body' => [
                    'en' => "Four sessions a week, and it has been that way for a couple of years now. Not for a number on a bar — for the fact that it is the one hour of my day with no notifications, no tickets and no half-finished branch waiting for me.\n\nIt also fixed something the code could not. Progress in the gym is slow, boring and completely honest, and getting used to that made me much better at long projects that do not reward you for the first three weeks.",
                    'cs' => "Čtyři tréninky týdně, a takhle to je už pár let. Ne kvůli číslu na čince — kvůli tomu, že je to jediná hodina dne bez notifikací, ticketů a rozdělané branch, která na mě čeká.\n\nSpravilo mi to i něco, co kód nedokázal. Pokrok v posilovně je pomalý, nudný a naprosto upřímný, a zvyknout si na to mě zlepšilo v dlouhých projektech, které vás první tři týdny nijak neodmění.",
                ],
                'pull' => [
                    'en' => 'Showing up on the bad days is a skill, and it transfers.',
                    'cs' => 'Ukázat se i ve špatný den je dovednost, a přenáší se dál.',
                ],
                'facts' => [
                    'en' => [
                        ['dt' => 'Frequency', 'dd' => '4× a week'],
                        ['dt' => 'Since', 'dd' => '2023'],
                        ['dt' => 'Phone policy', 'dd' => 'Locker'],
                    ],
                    'cs' => [
                        ['dt' => 'Frekvence', 'dd' => '4× týdně'],
                        ['dt' => 'Od', 'dd' => '2023'],
                        ['dt' => 'Politika telefonu', 'dd' => 'Skříňka'],
                    ],
                ],
                'meta' => ['en' => [], 'cs' => []],
            ],
            [
                'label' => ['en' => 'Travel', 'cs' => 'Cestování'],
                'title' => ['en' => 'Trains, and a route planned too carefully', 'cs' => 'Vlaky a až příliš pečlivě naplánovaná trasa'],
                'highlight' => ['en' => 'a route planned too carefully', 'cs' => 'příliš pečlivě naplánovaná trasa'],
                'body' => [
                    'en' => "I would rather spend eleven hours on a train than two in the air. Half of the trip, for me, happens before it starts: timetables, connections, one spreadsheet I refuse to show anyone, and a plan loose enough that a missed connection is a story instead of a problem.\n\nSix countries so far, mostly around central Europe, usually with a laptop I promised not to open. The part that stuck is small: everywhere I have been, things work because somebody bothered to make them work — the signage, the ticket machine, the timetable. It is hard to unsee that once you start building things yourself.",
                    'cs' => "Radši strávím jedenáct hodin ve vlaku než dvě hodiny v letadle. Půlka cesty se pro mě odehrává ještě předtím, než začne: jízdní řády, přípoje, jeden excel, který nikomu neukážu, a plán volný tak akorát, aby zmeškaný přípoj byl historka, ne problém.\n\nZatím šest zemí, hlavně po střední Evropě, obvykle s notebookem, který jsem slíbil neotevřít. Co mi z toho zůstalo, je drobnost: všude, kde jsem byl, věci fungují, protože se je někdo obtěžoval udělat funkční — značení, jízdenkový automat, jízdní řád. Jakmile začnete věci stavět sami, nejde si toho nevšímat.",
                ],
                'pull' => ['en' => null, 'cs' => null],
                'facts' => [
                    'en' => [
                        ['dt' => 'Countries', 'dd' => '6 and counting'],
                        ['dt' => 'Preferred', 'dd' => 'Rail over air'],
                        ['dt' => 'Next up', 'dd' => 'Northern route'],
                    ],
                    'cs' => [
                        ['dt' => 'Zemí', 'dd' => '6 a přibývá'],
                        ['dt' => 'Preference', 'dd' => 'Vlak před letadlem'],
                        ['dt' => 'Další v plánu', 'dd' => 'Severní trasa'],
                    ],
                ],
                'meta' => ['en' => [], 'cs' => []],
            ],
        ];

        foreach ($interests as $i => $interest) {
            Interest::create([...$interest, 'image_path' => null, 'sort_order' => $i]);
        }
    }
}
