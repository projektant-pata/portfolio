<?php

return [
    'email' => 'Write me',
    'cv' => 'Download CV',
    'fine' => 'Replies within 24 hours · CET',

    /*
     * One entry per public route name (dots become underscores, so
     * `blog.show` is `blog_show`). A page without an entry falls back to
     * `default`, which is what keeps a new route from rendering an empty band.
     */
    'pages' => [
        'default' => [
            'title' => 'Got something to <em>build</em>?',
            'sub' => 'Tell me what you need and you get an honest answer — including when I am not the right person for it.',
        ],
        'home' => [
            'title' => 'Seen enough? Let us <em>talk</em>.',
            'sub' => 'Two sentences about the idea are enough to start. Every message lands with me, not with a form robot.',
        ],
        'experience' => [
            'title' => 'Want that track record on <em>your</em> project?',
            'sub' => 'School, client and competition work since 2021. The next line in the timeline could be yours.',
        ],
        'projects' => [
            'title' => 'Your project could be <em>next</em> in this list.',
            'sub' => 'From a one-page site to a full Laravel app — send the brief and you get a plan back, not a sales pitch.',
        ],
        'blog' => [
            'title' => 'Read enough? <em>Write</em> back.',
            'sub' => 'Questions about a post, a correction, or work you want done — all three land in the same inbox.',
        ],
        'blog_show' => [
            'title' => 'If this one was useful, <em>say so</em>.',
            'sub' => 'Feedback on the article or a brief for something you want built — both are welcome.',
        ],
        'contact' => [
            'title' => 'Prefer it <em>short</em>?',
            'sub' => 'Skip the form. Mail, GitHub or LinkedIn get to me just as fast.',
        ],
    ],
];
