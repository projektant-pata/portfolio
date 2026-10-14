<?php

return [
    'email' => 'Napište mi',
    'cv' => 'Stáhnout CV',
    'fine' => 'Odpovídám do 24 hodin · SEČ',

    /*
     * Jeden záznam na každou veřejnou routu (tečka se mění na podtržítko,
     * takže `blog.show` je `blog_show`). Stránka bez záznamu spadne na
     * `default` — díky tomu nová routa nikdy nevykreslí prázdný pruh.
     */
    'pages' => [
        'default' => [
            'title' => 'Máte co <em>stavět</em>?',
            'sub' => 'Napište, co potřebujete, a dostanete upřímnou odpověď — včetně toho, když na to nejsem ten pravý.',
        ],
        'home' => [
            'title' => 'Viděli jste dost? Pojďme si <em>napsat</em>.',
            'sub' => 'Dvě věty o nápadu stačí. Každá zpráva chodí mně, ne formulářovému robotovi.',
        ],
        'experience' => [
            'title' => 'Chcete tuhle praxi na <em>svém</em> projektu?',
            'sub' => 'Škola, klienti a soutěže od roku 2021. Další řádek téhle časové osy může být váš.',
        ],
        'projects' => [
            'title' => 'Váš projekt může být v tomhle seznamu <em>další</em>.',
            'sub' => 'Od jednostránkového webu po celou Laravel aplikaci — pošlete zadání a vrátí se vám plán, ne obchodní řeči.',
        ],
        'blog' => [
            'title' => 'Přečteno? <em>Napište</em> mi.',
            'sub' => 'Dotaz k článku, oprava, nebo práce, kterou chcete udělat — všechno padá do stejné schránky.',
        ],
        'blog_show' => [
            'title' => 'Když tenhle článek pomohl, <em>dejte vědět</em>.',
            'sub' => 'Zpětná vazba k textu i zadání na něco, co chcete postavit — vítám obojí.',
        ],
        'contact' => [
            'title' => 'Chcete to <em>krátce</em>?',
            'sub' => 'Vynechte formulář. Mail, GitHub nebo LinkedIn se ke mně dostanou stejně rychle.',
        ],
    ],
];
