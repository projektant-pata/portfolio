<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social / external links
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the owner's external profile URLs. Consumed by
    | the desktop footer and the mobile nav, which render them with their own
    | icons and labels. Change a URL here and both surfaces update.
    |
    */

    'social' => [
        'email' => 'mailto:richard.hyvl@gmail.com',
        'instagram' => 'https://www.instagram.com/richardhyvl/',
        'x' => 'https://x.com/projektantPata',
        'linkedin' => 'https://www.linkedin.com/in/richardhyvl/',
        'github' => 'https://github.com/projektant-pata',
        'chess' => 'https://www.chess.com/member/obviouscommander',
    ],

    /*
    |--------------------------------------------------------------------------
    | Hero artwork
    |--------------------------------------------------------------------------
    |
    | One image per public page hero. All four point at the same portrait
    | until page-specific artwork exists — swapping one in later is a
    | one-line change here, with no template edit.
    |
    */

    'hero_images' => [
        'home' => 'images/home-hero.webp',
        'about' => 'images/about-hero.webp',
        'experience' => 'images/experience-hero.webp',

        /* Device shot in the Experience hero's dock column. Empty until the
           clean transparent export lands — the column then renders label-only. */
        'experience_dock' => '',
        'projects' => 'images/projects-hero.webp',
    ],

];
