<?php

test('the stats section is introduced by a section head', function () {
    $this->get(route('home'))
        ->assertSee('By the numbers')
        ->assertSee('Some of it is <em>serious</em>', false)
        ->assertSee("Two numbers I'd defend in an interview, and one I wouldn't.", false);
});

test('the stats ghost wordmark is decorative and does not repeat the title', function () {
    $this->get(route('home'))
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">My stats</div>', false)
        ->assertDontSee('<h2>My stats</h2>', false);
});

test('the stats head renders in Czech', function () {
    $this->withSession(['locale' => 'cs'])
        ->get(route('home'))
        ->assertSee('V číslech')
        ->assertSee('Něco z toho je <em>vážně</em>', false);
});

test('the projects head links out to the projects page', function () {
    $this->get(route('home'))
        ->assertSee('Selected work')
        ->assertSee('Things I <em>shipped</em>, not things I started', false)
        ->assertSee('<a href="'.route('projects').'">All projects →</a>', false);
});

test('the work and tools heads render without a ghost wordmark', function () {
    $response = $this->get(route('home'));

    $response
        ->assertSee('Track record')
        ->assertSee("Where I've <em>been</em> since 2021", false)
        ->assertSee('<a href="'.route('experience').'">Experience page</a>', false)
        ->assertSee('Daily drivers')
        ->assertSee('What I actually <em>open</em> every day', false)
        ->assertDontSee('>Work &amp; Life</div>', false)
        ->assertDontSee('>Tools</div>', false);
});

test('the reviews head renders with a ghost and no note', function () {
    $this->get(route('home'))
        ->assertSee('What people say')
        ->assertSee('Words from people who <em>worked</em> with me', false)
        ->assertSee('<div class="sechead-ghost" aria-hidden="true">Reviews</div>', false);
});

test('every h2 on the home page belongs to a section head', function () {
    $html = $this->get(route('home'))->getContent();

    // 5 section heads + the footer wordmark, which keeps its own oversized
    // treatment and is not a section head.
    expect(preg_match_all('/<h2[\s>]/', $html))->toBe(6)
        ->and(preg_match_all('/<p class="sechead-eyebrow">/', $html))->toBe(5)
        ->and($html)->toContain('<h2 class="portfolio-footer-watermark">');
});
