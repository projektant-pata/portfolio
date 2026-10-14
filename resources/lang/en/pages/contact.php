<?php

return [
    'hero_wordmark' => 'Contact me',
    'hero_tags' => ['Email', 'LinkedIn', 'GitHub', 'CET', 'Czech · English'],

    /* 01 · Direct channels */
    'ways_head_ghost' => 'Platforms',
    'ways_head_eyebrow' => 'Direct channels',
    'ways_head_title' => 'Skip the form if you would <em>rather not</em>',
    'ways_head_note' => 'They all reach me. Email is fastest, LinkedIn is fine for recruiters, GitHub is where the code lives.',
    'way_email_label' => 'Email',
    'way_email_note' => 'Best for anything with a brief, a repo or a deadline attached.',
    'way_linkedin_label' => 'LinkedIn',
    'way_linkedin_note' => 'Best for recruiters and anything that needs a formal CV attached.',
    'way_github_label' => 'GitHub',
    'way_github_note' => 'Best for code questions — open an issue and I will see it faster than an email.',
    'way_phone_label' => 'Phone',
    'way_phone_note' => 'Only once we have exchanged emails — I do not answer numbers I do not know.',
    'copy' => 'Copy',
    'copied' => 'Copied',
    'write_to_me' => 'Write to me',
    'open_profile' => 'Open profile',

    /* 03 · Form */
    'form_head_ghost' => 'Form',
    'form_head_eyebrow' => 'Write to me',
    'form_head_title' => 'Five fields, <em>no funnel</em>',
    'form_head_note' => 'Nothing is stored anywhere except my inbox. No newsletter, no tracking, no follow-up sequence.',
    'field_required' => 'required',
    'field_optional' => 'optional',
    'name_label' => 'Your name',
    'name_placeholder' => 'Jana Nováková',
    'email_label' => 'Email',
    'email_placeholder' => 'jana@firma.cz',
    'email_help' => 'I reply to this address and nothing else.',
    'topic_label' => 'I am writing about',
    'topic_freelance' => 'Freelance project',
    'topic_job' => 'Job or internship',
    'topic_hackathon' => 'Hackathon or team-up',
    'topic_question' => 'Question about a project',
    'topic_other' => 'Something else',
    'timeline_label' => 'Timeline',
    'timeline_placeholder' => 'e.g. start in September, done by December',
    'timeline_help' => 'A rough date beats an exact one you will change.',
    'message_label' => 'Message',
    'message_placeholder' => 'What are you building, what is broken, or what the role is. Links are welcome — repos, briefs, half-finished figma files.',
    'message_help' => 'Three sentences is plenty. Links welcome.',
    'consent' => 'I am fine with Richard keeping this message and my email address in order to reply. Nothing else happens with them — :link.',
    'consent_link' => 'privacy note',
    'form_fine' => 'Prefer email?',
    'send' => 'Send message',
    'sending' => 'Sending…',

    'privacy_title' => 'Privacy note',
    'privacy_body' => [
        'What you type here is emailed straight to my personal inbox. It is not written to a database, not sent to any third party and not fed to an analytics tool.',
        'I keep the message as long as the conversation is useful, and I delete it when it is not. Ask me to delete it sooner and I will.',
        'There is no newsletter to be signed up for and no follow-up sequence. The only thing you get back is my reply.',
    ],
    'privacy_close' => 'Close',

    'success_title' => 'Message sent. Talk soon.',
    'success_body' => 'It landed in my inbox — you will hear from me within 24 hours. If it is urgent, email :email directly.',
    'failure_title' => 'That did not go through.',
    'failure_body' => 'Something broke on my side, not yours. Please send the same text to :email.',
    'copy_message' => 'Copy message',

    'err_name' => 'Add your name so I know who I am replying to.',
    'err_email' => 'That email address is not going to work.',
    'err_message' => 'A sentence or two, at minimum.',
    'err_consent' => 'Tick the box and I can reply.',
    'err_summary' => 'The form was not sent — see the fields below.',

    /* Rail */
    'rail_title' => 'Before you hit send',
    'rail_items' => [
        '<b>Say what it is for.</b> One sentence about the goal tells me more than a feature list.',
        '<b>Name the constraint.</b> A date, a budget range or an existing stack — whichever is fixed.',
        '<b>Link what exists.</b> Repo, staging URL, screenshot. I would rather read code than a description of it.',
        '<b>Skip the NDA for now.</b> The first email does not need one; the second one can.',
    ],
    'rail_note' => 'Recruiters: role, stack, location and whether it is an internship or a junior position is enough for a first message. I will send the CV back.',

    /* 04 · What happens next */
    'steps_head_ghost' => 'Steps',
    'steps_head_eyebrow' => 'What happens next',
    'steps_head_title' => 'Three steps, <em>no black box</em>',
    'steps_head_note' => 'Written down so you know when to chase me — and when there is nothing to chase.',
    'steps' => [
        [
            'when' => 'immediately',
            'title' => 'Confirmation',
            'body' => 'An automatic email with a copy of what you sent, so you have it in writing. If it does not arrive, the address was mistyped.',
        ],
        [
            'when' => 'within 24 hours',
            'title' => 'A written answer from me',
            'body' => 'Yes, no, or “yes but not before this date”. If it is a no, you get the reason and, where I know one, a better contact.',
        ],
        [
            'when' => '20 minutes',
            'title' => 'A call, if it earns one',
            'body' => 'Only when writing stops being efficient. Twenty minutes, camera optional, and I send a short summary afterwards.',
        ],
    ],

    /* 06 · FAQ */
    'faq_head_ghost' => 'FAQ',
    'faq_head_eyebrow' => 'Before you ask',
    'faq_head_title' => 'The five questions <em>that always come</em>',
    'faq_head_note' => 'Answered here so the first email can be about your project instead.',
    'faq' => [
        [
            'q' => 'Are you available right now?',
            'a' => 'Yes, for <b>part-time and freelance work</b> alongside school, and for an internship from the summer. The status dot at the top of this page is the source of truth — if it is green, I have capacity.',
        ],
        [
            'q' => 'What do you charge?',
            'a' => 'For small projects I quote <b>a fixed price per piece of work</b> after one call, not an hourly rate — you know the number before anything starts. Student and non-profit work is cheaper; say so in the first email.',
        ],
        [
            'q' => 'What do you actually build?',
            'a' => 'Backend and full-stack web: <b>Java, PHP, Postgres, a bit of self-hosting</b>. I do not do native mobile apps, and I am not a designer — though I will happily build from a design you already have.',
        ],
        [
            'q' => 'Where are you, and does it matter?',
            'a' => '<b>Czech Republic, CET.</b> Remote is normal; anything inside a two-hour train ride I can do in person. Overlap with US mornings is fine, US afternoons are not.',
        ],
        [
            'q' => 'Will you do it for free / for exposure?',
            'a' => 'For <b>open source and student projects</b>, sometimes — ask. For commercial work, no, but the first conversation is always free and I will tell you if the thing you want is cheaper to buy than to build.',
        ],
    ],
];
