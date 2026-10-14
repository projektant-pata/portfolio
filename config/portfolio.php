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
    | Contact channels
    |--------------------------------------------------------------------------
    |
    | The contact page's channel list renders one row per non-empty entry, so
    | leaving `phone` blank drops the phone row rather than shipping a
    | placeholder number. `email` is the bare address — the mailto: form lives
    | in `social.email` above and both must point at the same inbox.
    |
    | `inbox` is where the contact form delivers; it defaults to `email`.
    |
    */

    'contact' => [
        'email' => 'richard.hyvl@gmail.com',
        'inbox' => env('CONTACT_INBOX', 'richard.hyvl@gmail.com'),
        'phone' => '+420 730 815 882',
        'linkedin_handle' => '/in/richardhyvl',
        'github_handle' => 'github.com/projektant-pata',
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

        /* No contact crop exists yet — the about portrait stands in. */
        'contact' => 'images/about-hero.webp',

        /* No blog crop exists yet — the Experience portrait stands in. */
        'blog' => 'images/experience-hero.webp',

        /* Intro portrait on the about page. Empty until the 900×1200 crop
           lands — the intro renders the placeholder plate meanwhile. */
        'portrait' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | Section artwork
    |--------------------------------------------------------------------------
    |
    | Images owned by a single section rather than by a hero. The home
    | Work & Life plate swaps its photo with the scope toggle; an empty
    | entry renders the striped placeholder plate instead of a broken image.
    |
    */

    'section_images' => [
        'work_life_work' => 'images/home/work-life-work.png',
        'work_life_life' => '',
    ],

    /*
    |--------------------------------------------------------------------------
    | CV
    |--------------------------------------------------------------------------
    |
    | Path (relative to public/) of the downloadable CV. The about page's
    | contact band renders its "Download CV" button only when this is set —
    | a download button pointing at nothing is worse than no button.
    |
    */

    'cv_path' => '',

    /*
    |--------------------------------------------------------------------------
    | Easter eggs
    |--------------------------------------------------------------------------
    |
    | Fun, non-navigational links for the mobile nav's playful icons.
    |
    */

    'easter_eggs' => [
        // Temp: Wayback Machine copy of the father's old site (hellyeah.md).
        // Swap for a self-hosted copy later.
        'fathers_site' => 'https://web.archive.org/web/20201205183732/http://www.megasphera.cz/hym/',
    ],

];
