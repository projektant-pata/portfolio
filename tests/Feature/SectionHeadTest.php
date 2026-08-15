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
