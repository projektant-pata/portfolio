<?php

namespace Database\Seeders;

use App\Models\AboutCard;
use Illuminate\Database\Seeder;

class AboutCardSeeder extends Seeder
{
    public function run(): void
    {
        AboutCard::query()->delete();

        $cards = [
            [
                'title' => ['en' => 'About me', 'cs' => 'O mně'],
                'text' => [
                    'en' => 'Hi there! I\'m Richard Hývl, a starting software developer and freelancer with a passion. Currently, I\'m a student at SPŠE Pardubice and leading figure in a Web developing group called <span>Prezz.</span>',
                    'cs' => 'Ahoj! Jsem Richard Hývl, začínající softwarový vývojář a freelancer s vášní. V současné době jsem studentem na SPŠE Pardubice a vůdčí osobností skupiny webových vývojářů <span>Prezz.</span>',
                ],
            ],
            [
                'title' => ['en' => 'What do I like?', 'cs' => 'Co mám rád?'],
                'text' => [
                    'en' => 'From a young age I was a passionate chess player. I was winning in 2nd grade against highschoolers at local chess tournaments. With a great break I\'m back, with a renewed passion for the game. Chess has taught me <span>critical thinking</span>, <span>strategy</span>, and the important <span>patience</span> — skills that I\'ve found incredibly valuable in my journey as a software developer.<br><br>I also really really love <span>catfishes</span> and <span>Rock music</span> :)',
                    'cs' => 'Od mládí jsem byl vášnivým šachistou. Ve 2. třídě jsem vyhrával proti středoškolákům na místním šachovém turnaji. S velkou přestávkou jsem zpět, s obnovenou vášní pro hru. Šachy mě naučily <span>kritickému myšlení</span>, <span>strategii</span> a důležité <span>trpělivosti</span> — dovednostem, které považuji za neuvěřitelně cenné na své cestě softwarového vývojáře.<br><br>Mám také opravdu moc rád <span>sumečky</span> a <span>rockovou hudbu</span> :)',
                ],
            ],
            [
                'title' => ['en' => 'What drives me?', 'cs' => 'Co mě pohání?'],
                'text' => [
                    'en' => 'I\'m driven by curiosity and the desire to help other people. I thrive on learning, growing my personality and one day becoming a successful person.',
                    'cs' => 'Pohání mě zvědavost a touha pomáhat druhým lidem. Rád se učím, rozvíjím svou osobnost a jednou budu úspěšný člověk.',
                ],
            ],
            [
                'title' => ['en' => 'How did we get here?', 'cs' => 'Jak jsme se sem dostali?'],
                'text' => [
                    'en' => 'My journey started after I went back to elementary school from gymnasium due to health issues. Luckily the elementary school had extended teaching in the field of IT.<br><br>There, I fell in love with the technology, the unlimited options to create and innovate new things — it was like a dream. There, I developed my first website. Sadly, it was deleted by the hosting and I had no backup.<br><br>At high school, my passion thrived even more, leading me to achieve multiple victories, like winning a hackathon and becoming a freelancer.',
                    'cs' => 'Moje cesta začala poté, co jsem se kvůli zdravotním problémům vrátil z gymnázia na základní školu. Naštěstí měla základní škola rozšířenou výuku v oboru IT.<br><br>Tam jsem se zamiloval do technologií, do neomezených možností vytvářet a inovovat nové věci — bylo to jako sen. Tam jsem vytvořil své první webové stránky. Bohužel je hosting smazal a já neměl žádnou zálohu.<br><br>Na střední škole se mé nadšení rozmohlo ještě víc, což mě dovedlo k několika vítězstvím, jako například vyhrát hackathon a stát se freelancerem.',
                ],
            ],
            [
                'title' => ['en' => 'Volunteering?', 'cs' => 'Dobrovolnictví?'],
                'text' => [
                    'en' => 'I have volunteered at a few community events. It helped me develop presenting skills.<br><br>What was I part of:<ul><li><p><span>PEER program:</span> A program that helps teenagers understand the dangers of drugs and bullying. I was educated and then presented to my peers.</p></li><li><p><span>CZECH DAY AGAINST CANCER:</span> An organisation that collects funds to help people with cancer by selling flower badges on streets.</p></li></ul>',
                    'cs' => 'Dobrovolně jsem se účastnil několika komunitních akcí. Pomohlo mi to rozvíjet prezentační dovednosti.<br><br>Čeho jsem byl součástí:<ul><li><p><span>Program PEER:</span> Program, který pomáhá dospívajícím pochopit nebezpečí drog a šikany. Byl jsem vzdělán a poté jsem prezentoval svým vrstevníkům.</p></li><li><p><span>ČESKÝ DEN PROTI RAKOVINĚ:</span> Organizace vytvořená za účelem sbírání finančních prostředků na pomoc lidem s rakovinou prostřednictvím prodeje květinových odznaků na ulicích.</p></li></ul>',
                ],
            ],
        ];

        foreach ($cards as $i => $card) {
            AboutCard::create([
                'title' => $card['title'],
                'text' => $card['text'],
                'sort_order' => $i,
            ]);
        }
    }
}
